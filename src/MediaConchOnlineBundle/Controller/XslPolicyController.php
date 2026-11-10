<?php

namespace MediaConchOnlineBundle\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use MediaConchOnlineBundle\Form\Type\XslPolicyCreateFromFileFormType;
use MediaConchOnlineBundle\Form\Type\XslPolicyImportFormType;
use MediaConchOnlineBundle\Form\Type\XslPolicyInfoFormType;
use MediaConchOnlineBundle\Form\Type\XslPolicyRuleFormType;
use MediaConchOnlineBundle\Form\Type\XslPolicyRuleMtFormType;
use MediaConchOnlineBundle\Lib\MediaConch\InitInstanceId;
use MediaConchOnlineBundle\Lib\MediaConch\MediaConchServerException;
use MediaConchOnlineBundle\Lib\Checker\CheckerAnalyze;
use MediaConchOnlineBundle\Lib\Checker\CheckerStatus;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyCreate;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyDelete;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyDuplicate;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyEdit;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyEditType;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyEditVisibility;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyExport;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyFormFields;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyFormValues;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyFromFile;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyGetPolicies;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyGetPolicy;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyGetPolicyName;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyGetRule;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyImport;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyMove;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyRuleCreate;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyRuleDelete;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyRuleDuplicate;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyRuleEdit;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicyRuleMove;
use MediaConchOnlineBundle\Lib\XslPolicy\XslPolicySave;
use UserBundle\Lib\Quotas\Quotas;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[Route('/MediaConchOnline')]
class XslPolicyController extends BaseController
{
    /**
     * Old policy editor page.
     */
    #[Route('/xslPolicyTree/')]
    public function xslPolicyTreeOldAction()
    {
        return $this->redirectToRoute('mco_policy_tree', [], 301);
    }

    /**
     * Policy editor page.
     */
    #[Route('/policyEditor', name: 'mco_policy_tree')]
    #[Template('@MediaConchOnline/XslPolicy/xslPolicyTree.html.twig')]
    public function xslPolicyTreeAction(Quotas $quotas)
    {
        // Forms
        $policyRuleForm = $this->createForm(XslPolicyRuleFormType::class);
        $policyRuleMtForm = $this->createForm(XslPolicyRuleMtFormType::class);
        $policyInfoForm = $this->createForm(XslPolicyInfoFormType::class);

        if ($quotas->hasPolicyCreationRights()) {
            $importPolicyForm = $this->createForm(XslPolicyImportFormType::class);
            $policyCreateFromFileForm = $this->createForm(XslPolicyCreateFromFileFormType::class);
        }

        return [
            'policyRuleForm' => $policyRuleForm->createView(),
            'policyRuleMtForm' => $policyRuleMtForm->createView(),
            'importPolicyForm' => isset($importPolicyForm) ? $importPolicyForm->createView() : false,
            'policyCreateFromFileForm' => isset($policyCreateFromFileForm) ? $policyCreateFromFileForm->createView() : false,
            'policyInfoForm' => $policyInfoForm->createView(),
        ];
    }

    /**
     * Get the json for jstree.
     *
     * @return json
     *              {"policiesTree":POLICIES_JSTREE_JSON}
     */
    #[Route('/xslPolicyTree/ajax/data', name: 'mco_policy_tree_data')]
    public function xslPolicyTreeDataAction(Request $request, XslPolicyGetPolicies $policies, InitInstanceId $init)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Init MediaConch-Server-ID
        $init->init();

        try {
            $policies->getPolicies([], 'JSTREE');

            return new JsonResponse(['policiesTree' => $policies->getResponse()->getPolicies()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Create a policy.
     *
     * @param int parentId policy ID in which the new policy will be created
     *
     * @return json
     *              {"policy":POLICY_JSTREE_JSON}
     */
    #[Route(
        '/xslPolicyTree/ajax/create/{parentId}',
        name: 'mco_policy_create',
        requirements: ['parentId' => '(-)?\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeCreateAction(
        $parentId,
        Request $request,
        Quotas $quotas,
        XslPolicySave $policySave,
        XslPolicyGetPolicy $policy,
        XslPolicyCreate $policyCreate
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Check quota only if policy is created on the top level
        if (-1 == $parentId && !$quotas->hasPolicyCreationRights()) {
            return new JsonResponse([
                'message' => 'Quota exceeded',
                'quota' => $this->renderView('@MediaConchOnline/Default/quotaExceeded.html.twig'),
            ], 400);
        }

        try {
            // Create policy
            $policyCreate->create($parentId);

            // Save policy
            $policySave->save($policyCreate->getCreatedId());

            // Get policy
            $policy->getPolicy($policyCreate->getCreatedId(), 'JSTREE');

            return new JsonResponse(['policy' => $policy->getResponse()->getPolicy()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Import a policy from an XML (the XML is provided as POST data from a form).
     *
     * @return json
     *              {"policy":POLICY_JSTREE_JSON}
     */
    #[Route('/xslPolicyTree/ajax/import', name: 'mco_policy_import', methods: ['POST'])]
    public function xslPolicyTreeImportAction(
        Request $request,
        Quotas $quotas,
        XslPolicyImport $policyImport,
        XslPolicySave $policySave,
        XslPolicyGetPolicy $policy
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Check quota
        if (!$quotas->hasPolicyCreationRights()) {
            return new JsonResponse([
                'message' => 'Quota exceeded',
                'quota' => $this->renderView('@MediaConchOnline/Default/quotaExceeded.html.twig'),
            ], 400);
        }

        $importPolicyForm = $this->createForm(XslPolicyImportFormType::class);
        $importPolicyForm->handleRequest($request);
        if ($importPolicyForm->isSubmitted() && $importPolicyForm->isValid()) {
            $data = $importPolicyForm->getData();
            if ($data['policyFile']->isValid()) {
                try {
                    // Import policy
                    $policyImport->import(file_get_contents($data['policyFile']->getRealPath()));

                    // Save policy
                    $policySave->save($policyImport->getCreatedId());

                    // Get policy
                    $policy->getPolicy($policyImport->getCreatedId(), 'JSTREE');

                    return new JsonResponse(['policy' => $policy->getResponse()->getPolicy()], 200);
                } catch (MediaConchServerException $e) {
                    return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
                }
            }
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }

    /**
     * Create a policy from a file (the file is provided as POST data from a form).
     *
     * @return json
     *              {"policy":POLICY_JSTREE_JSON}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    #[Route('/xslPolicyTree/ajax/createFromFile', name: 'mco_policy_create_from_file', methods: ['POST'])]
    public function xslPolicyTreeCreateFromFileAction(
        Request $request,
        Quotas $quotas,
        CheckerAnalyze $checks,
        CheckerStatus $status,
        XslPolicyFromFile $policyFromFile,
        XslPolicySave $policySave,
        XslPolicyGetPolicy $policy
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Check quota
        if (!$quotas->hasPolicyCreationRights()) {
            return new JsonResponse([
                'message' => 'Quota exceeded',
                'quota' => $this->renderView('@MediaConchOnline/Default/quotaExceeded.html.twig'),
            ], 400);
        }

        $policyCreateFromFileForm = $this->createForm(XslPolicyCreateFromFileFormType::class);
        $policyCreateFromFileForm->handleRequest($request);
        if ($policyCreateFromFileForm->isSubmitted() && $policyCreateFromFileForm->isValid()) {
            $data = $policyCreateFromFileForm->getData();
            if ($data['file']->isValid()) {
                $path = $this->getParameter('kernel.project_dir').'/files/uploadTmp/'.$this->getUser()->getId();
                $filename = $data['file']->getClientOriginalName();
                $file = $data['file']->move($path.'/', $filename);

                try {
                    // Analyze file
                    $checks->analyse([$file->getRealPath()]);
                    $response = $checks->getResponseAsArray();
                    $transactionId = $response[0]['transactionId'];

                    // Wait for analyze is complete
                    usleep(200000);
                    for ($loop = 5; $loop >= 0; --$loop) {
                        $status->getStatus($transactionId);
                        $response = $status->getResponse();
                        // Stop the loop when analyze is finish
                        if (isset($response[$transactionId]['finish']) && true === $response[$transactionId]['finish']) {
                            $loop = 0;
                        } elseif (0 == $loop) {
                            throw new MediaConchServerException('Analyze is not finish', 400);
                        } else {
                            usleep(500000);
                        }
                    }

                    // Remove tmp file
                    unlink($file);

                    // Generate policy
                    $policyFromFile->getPolicy($transactionId);

                    // Save policy
                    $policySave->save($policyFromFile->getCreatedId());

                    // Get policy
                    $policy->getPolicy($policyFromFile->getCreatedId(), 'JSTREE');

                    return new JsonResponse(['policy' => $policy->getResponse()->getPolicy()], 200);
                } catch (MediaConchServerException $e) {
                    // Remove tmp file
                    unlink($file);

                    return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
                }
            }
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }

    /**
     * Export XML of a policy.
     *
     * @param int id policy ID of the policy to export
     *
     * @return XML
     */
    #[Route('/xslPolicyTree/export/{id}', name: 'mco_policy_export', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function xslPolicyTreeExportAction($id, XslPolicyGetPolicyName $policyName, XslPolicyExport $policyExport)
    {
        try {
            // Get policy name
            $policyName->getPolicyName($id);
            $policyName = $policyName->getResponse()->getName();

            // Get policy XML
            $policyExport->export($id);

            // Prepare response
            $response = new Response($policyExport->getPolicyXml());
            $disposition = $this->downloadFileDisposition($response, $policyName.'.xml');

            $response->headers->set('Content-Type', 'text/xml');
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-length', strlen($policyExport->getPolicyXml()));

            return $response;
        } catch (MediaConchServerException $e) {
            throw new ServiceUnavailableHttpException();
        }
    }

    /**
     * Edit a policy (POST data from a form).
     *
     * @param int id policy ID of the policy to edit
     *
     * @return json
     *              {"policy":POLICY_JSTREE_JSON}
     */
    #[Route('/xslPolicyTree/ajax/edit/{id}', name: 'mco_policy_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function xslPolicyTreeEditAction(
        $id,
        Request $request,
        XslPolicyEdit $policyEdit,
        XslPolicyEditType $policyEditType,
        XslPolicyEditVisibility $policyEditVisibility,
        XslPolicySave $policySave,
        XslPolicyGetPolicy $policy
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $policyEditForm = $this->createForm(XslPolicyInfoFormType::class);
        $policyEditForm->handleRequest($request);
        if ($policyEditForm->isSubmitted() && $policyEditForm->isValid()) {
            $data = $policyEditForm->getData();

            try {
                // Edit policy name and description
                $policyEdit->edit($id, $data['policyName'], $data['policyDescription'], $data['policyLicense']);

                // Edit policy type
                $policyEditType->editType($id, $data['policyType']);

                // Edit policy visibility if policy is top level
                if (1 == $data['policyTopLevel'] && $this->isGranted('ROLE_BASIC')) {
                    $policyEditVisibility->editVisibility($id, $data['policyVisibility']);
                }

                // Save policy
                $policySave->save($id);

                // Get policy
                $policy->getPolicy($id, 'JSTREE');

                return new JsonResponse(['policy' => $policy->getResponse()->getPolicy()], 200);
            } catch (MediaConchServerException $e) {
                return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
            }
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }

    /**
     * Duplicate a policy.
     *
     * @param int id policy ID of the policy to duplicate
     * @param int dstPolicyId policy ID of the destination policy
     *
     * @return json
     *              {"policy":POLICY_JSTREE_JSON}
     */
    #[Route(
        '/xslPolicyTree/ajax/duplicate/{id}/{dstPolicyId}',
        name: 'mco_policy_duplicate',
        requirements: ['id' => '\d+', 'dstPolicyId' => '(-)?\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeDuplicateAction(
        $id,
        $dstPolicyId,
        Request $request,
        Quotas $quotas,
        XslPolicyDuplicate $policyDuplicate,
        XslPolicySave $policySave,
        XslPolicyGetPolicy $policy
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Check quota only if policy is duplicated on the top level
        if (-1 == $dstPolicyId && !$quotas->hasPolicyCreationRights()) {
            return new JsonResponse([
                'message' => 'Quota exceeded',
                'quota' => $this->renderView('@MediaConchOnline/Default/quotaExceeded.html.twig'),
            ], 400);
        }

        try {
            // Duplicate policy
            $policyDuplicate->duplicate($id, $dstPolicyId);

            // Save policy
            $policySave->save($policyDuplicate->getCreatedId());

            // Get policy
            $policy->getPolicy($policyDuplicate->getCreatedId(), 'JSTREE');

            return new JsonResponse(['policy' => $policy->getResponse()->getPolicy()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Move a policy.
     *
     * @param int id policy ID of the policy to duplicate
     * @param int dstPolicyId policy ID of the destination policy
     *
     * @return json
     *              {"policy":POLICY_JSTREE_JSON}
     */
    #[Route(
        '/xslPolicyTree/ajax/move/{id}/{dstPolicyId}',
        name: 'mco_policy_move',
        requirements: ['id' => '\d+', 'dstPolicyId' => '(-)?\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeMoveAction(
        $id,
        $dstPolicyId,
        Request $request,
        XslPolicyMove $policyMove,
        XslPolicySave $policySave,
        XslPolicyGetPolicy $policy
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        try {
            // Move policy
            $policyMove->move($id, $dstPolicyId);

            // Save policy
            $policySave->save($policyMove->getCreatedId());

            // Get policy
            $policy->getPolicy($policyMove->getCreatedId(), 'JSTREE');

            return new JsonResponse(['policy' => $policy->getResponse()->getPolicy()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Delete a policy.
     *
     * @param int id policy ID of the policy to duplicate
     *
     * @return json
     *              {"policyId":ID}
     */
    #[Route(
        '/xslPolicyTree/ajax/delete/{id}',
        name: 'mco_policy_delete',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeDeleteAction($id, Request $request, XslPolicyDelete $policyDelete)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        try {
            // Delete policy
            $policyDelete->delete($id);

            return new JsonResponse(['policyId' => $id], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Add a rule to a policy.
     *
     * @param int policyId policy ID of the policy that will contain the rule
     *
     * @return json
     *              {"rule":{"tracktype":TRACKTYPE, "field":FIELD, "id":RULE_ID, "name":NAME, "value":VALUE, "occurrence":OCCURENCE, "ope":OPERATOR}}
     */
    #[Route(
        '/xslPolicyTree/ajax/ruleCreate/{policyId}',
        name: 'mco_policy_rule_create',
        requirements: ['policyId' => '\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeRuleCreateAction(
        $policyId,
        Request $request,
        XslPolicyRuleCreate $ruleCreate,
        XslPolicySave $policySave,
        XslPolicyGetRule $rule
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        try {
            // Create rule
            $ruleCreate->create($policyId);

            // Save policy
            $policySave->save($policyId);

            // Get rule
            $rule->getRule($ruleCreate->getCreatedId(), $policyId);

            return new JsonResponse(['rule' => $rule->getResponse()->getRule()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Edit a rule (POST data from a form).
     *
     * @param int id rule ID of the rule to edit
     * @param int policyId policy ID of the policy that contain the rule
     *
     * @return json
     *              {"rule":{"tracktype":TRACKTYPE, "field":FIELD, "id":RULE_ID, "name":NAME, "value":VALUE, "occurrence":OCCURENCE, "ope":OPERATOR}}
     */
    #[Route(
        '/xslPolicyTree/ajax/ruleEdit/{id}/{policyId}',
        name: 'mco_policy_rule_edit',
        requirements: ['id' => '\d+', 'policyId' => '\d+'],
        methods: ['POST'],
    )]
    public function xslPolicyTreeRuleEditAction(
        $id,
        $policyId,
        Request $request,
        XslPolicyRuleEdit $ruleEdit,
        XslPolicySave $policySave,
        XslPolicyGetRule $rule
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Get the requested form
        if ($request->request->has('xslPolicyRuleMt')) {
            $policyRuleForm = $this->createForm(XslPolicyRuleMtFormType::class);
        } else {
            $policyRuleForm = $this->createForm(XslPolicyRuleFormType::class);
        }

        $policyRuleForm->handleRequest($request);
        if ($policyRuleForm->isSubmitted() && $policyRuleForm->isValid()) {
            $data = $policyRuleForm->getData();

            try {
                // Edit rule
                $ruleEdit->edit($id, $policyId, $data);

                // Save policy
                $policySave->save($policyId);

                // Get rule
                $rule->getRule($id, $policyId);

                return new JsonResponse(['rule' => $rule->getResponse()->getRule()], 200);
            } catch (MediaConchServerException $e) {
                return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
            }
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }

    /**
     * Delete a rule.
     *
     * @param int id rule ID of the rule to delete
     * @param int policyId policy ID of the policy that contain the rule
     *
     * @return json
     *              {"id":RULE_ID}
     */
    #[Route(
        '/xslPolicyTree/ajax/ruleDelete/{id}/{policyId}',
        name: 'mco_policy_rule_delete',
        requirements: ['id' => '\d+', 'policyId' => '\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeRuleDeleteAction(
        $id,
        $policyId,
        Request $request,
        XslPolicyRuleDelete $ruleDelete,
        XslPolicySave $policySave
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        try {
            // Delete rule
            $ruleDelete->delete($id, $policyId);

            // Save policy
            $policySave->save($policyId);

            return new JsonResponse(['id' => $id], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Duplicate a rule.
     *
     * @param int id rule ID of the rule to duplicate
     * @param int policyId policy ID of the policy that contain the rule
     * @param int dstPolicyId policy ID of the destination policy
     *
     * @return json
     *              {"rule":{"tracktype":TRACKTYPE, "field":FIELD, "id":RULE_ID, "name":NAME, "value":VALUE, "occurrence":OCCURENCE, "ope":OPERATOR}}
     */
    #[Route(
        '/xslPolicyTree/ajax/ruleDuplicate/{id}/{policyId}/{dstPolicyId}',
        name: 'mco_policy_rule_duplicate',
        requirements: ['id' => '\d+', 'policyId' => '\d+', 'dstPolicyId' => '(-)?\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeRuleDuplicateAction(
        $id,
        $policyId,
        $dstPolicyId,
        Request $request,
        XslPolicyRuleDuplicate $ruleDuplicate,
        XslPolicySave $policySave,
        XslPolicyGetRule $rule
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        try {
            // Duplicate rule
            $ruleDuplicate->duplicate($id, $policyId, $dstPolicyId);

            // Save policy
            $policySave->save($policyId);

            // Get rule
            $rule->getRule($ruleDuplicate->getCreatedId(), $dstPolicyId);

            return new JsonResponse(['rule' => $rule->getResponse()->getRule()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Move a rule.
     *
     * @param int id rule ID of the rule to move
     * @param int policyId policy ID of the policy that contain the rule
     * @param int dstPolicyId policy ID of the destination policy
     *
     * @return json
     *              {"rule":{"tracktype":TRACKTYPE, "field":FIELD, "id":RULE_ID, "name":NAME, "value":VALUE, "occurrence":OCCURENCE, "ope":OPERATOR}}
     */
    #[Route(
        '/xslPolicyTree/ajax/ruleMove/{id}/{policyId}/{dstPolicyId}',
        name: 'mco_policy_rule_move',
        requirements: ['id' => '\d+', 'policyId' => '\d+', 'dstPolicyId' => '(-)?\d+'],
        methods: ['GET'],
    )]
    public function xslPolicyTreeRuleMoveAction(
        $id,
        $policyId,
        $dstPolicyId,
        Request $request,
        XslPolicyRuleMove $ruleMove,
        XslPolicySave $policySave,
        XslPolicyGetRule $rule
    ) {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        try {
            // Move rule
            $ruleMove->move($id, $policyId, $dstPolicyId);

            // Save policy
            $policySave->save($dstPolicyId);
            $policySave->save($policyId);

            // Get rule
            $rule->getRule($ruleMove->getCreatedId(), $dstPolicyId);

            return new JsonResponse(['rule' => $rule->getResponse()->getRule()], 200);
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }

    /**
     * Get list of fields for a trackType (POST : type and field).
     *
     * @return json
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    #[Route('/xslPolicy/fieldsListRule', name: 'mco_policy_rule_fields_list', methods: ['POST'])]
    public function xslPolicyRuleFieldsListAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Get the type value
        $type = $request->request->get('type');

        // Get the field value
        $field = $request->request->get('field', null);

        return new JsonResponse(XslPolicyFormFields::getFields($type, $field));
    }

    /**
     * Get list of values for a trackType and a field (POST : type, field and value).
     *
     * @return json
     */
    #[Route('/xslPolicyTree/ajax/valueListRule', name: 'mco_policy_rule_values_list', methods: ['POST'])]
    public function xslPolicyRuleValuesListAction(Request $request, XslPolicyFormValues $valuesList)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Get the type value
        $type = $request->request->get('type');

        // Get the field value
        $field = $request->request->get('field');

        // Get the value
        $value = $request->request->get('value');

        try {
            $valuesList->getValues($type, $field, $value);

            return new JsonResponse($valuesList->getResponseAsArray());
        } catch (MediaConchServerException $e) {
            return new JsonResponse(['message' => 'Error'], $e->getStatusCode());
        }
    }
}
