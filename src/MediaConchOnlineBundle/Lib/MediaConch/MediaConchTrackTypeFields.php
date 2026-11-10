<?php

namespace MediaConchOnlineBundle\Lib\MediaConch;

use Symfony\Component\Process\Process;

class MediaConchTrackTypeFields extends MediaConch
{
    public function __construct()
    {
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function run($trackType)
    {
        $process = Process::fromShellCommandline($this->MediaConch.' --MAXML_Fields='.$trackType);

        $process->run();

        if ($process->isSuccessful()) {
            $this->success = true;
            $this->output = trim($process->getOutput());
        }

        $this->output = trim($process->getOutput());

        return $this;
    }
}
