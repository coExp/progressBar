<?php

namespace coExp\ProgressBar;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBar
{
    /** @var  OutputInterface */
    protected $output;

    # Length of the progression bar
    const PROGRESSION_LENGTH = 50;
    # Clear the line from the cursor
    const CLEAR_LINE = "\e[2K";

    /**
     * @var  int
     */
    protected $nbrSuccess, $nbrPassed, $nbrWarning, $nbrError = 0;

    /**
     * Nbr of things to do
     * @var  int
     */
    protected $length;

    /**
     * Time of begin of command
     * @var int|null
     */
    protected $time = null;

    /**
     * Last time progress bas has been shown
     * @var int|null
     */
    protected $lastShown = null;

    /**
     * ProgressBar constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('ok',  new OutputFormatterStyle('black', 'green', ['bold']));
        $output->getFormatter()->setStyle('err', new OutputFormatterStyle('red', 'black', ['bold']));

        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->setErrorOutput($output);
        }

        $this->output = $output;
        $this->time = time();
    }

    /**
     * @param $length
     * @return $this
     */
    public function setLength($length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @param int $i
     * @return $this
     */
    public function addNbrPassed(int $i = 1): self
    {
        $this->nbrPassed += $i;

        return $this;
    }

    /**
     * @param int $i
     * @return $this
     */
    public function addNbrDone(int $i = 1): self
    {
        $this->nbrSuccess += $i;

        return $this;
    }

    /**
     * @param int $i
     * @return $this
     */
    public function addNbrWarning(int $i = 1): self
    {
        $this->nbrWarning += $i;

        return $this;
    }

    /**
     * @param int $i
     * @return $this
     */
    public function addNbrError(int $i = 1): self
    {
        $this->nbrError += $i;

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function write(string $message): self
    {
        $this->writeBlankLine();
        $this->output->write($message);
        $this->writeProgression(true);

        return $this;
    }

    /**
     * Write a line on output
     * @param string $message
     * @return $this
     */
    public function writeLine(string $message): self
    {
        $this->writeBlankLine();
        $this->output->writeln($message);
        $this->writeProgression(true);

        return $this;
    }

    /**
     * Erase the current output line
     */
    public function writeBlankLine(): self
    {
        self::blankLine($this->output);

        return $this;
    }

    /**
     * @param OutputInterface $output
     */
    public static function blankLine(OutputInterface $output)
    {
        try {
            $output->write("\r\e[2K");
        } catch (\Exception $e) {
            # Do not break your stuff
        }
    }

    /**
     * Show a cool and nice progression bar!
     * @param bool $force
     * @return $this
     */
    public function writeProgression(bool $force = false): self
    {
        # Show Progress bar each second
        if (!$force && $this->lastShown == time()) {
            return $this;
        }

        if ($this->length == 0) {
            return $this;
        }

        try {
            $nbrRemaining = $this->length - $this->getNbrDone();

            $timeElapsed = time() - $this->time;
            $timeETA = $nbrRemaining * ($timeElapsed / $this->getNbrSuccess(true));

            $percentPassed  = ($this->nbrPassed / $this->length);
            $percentWarning = ($this->nbrWarning / $this->length);
            $percentDone    = ($this->nbrSuccess / $this->length);
            $percentError   = ($this->nbrError / $this->length);

            $percent = $this->getNbrDone() / $this->length;

            $lengthPassed   = (int) ($percentPassed  * self::PROGRESSION_LENGTH);
            $lengthWarning  = (int) ($percentWarning * self::PROGRESSION_LENGTH);
            $lengthDone     = (int) ($percentDone    * self::PROGRESSION_LENGTH);
            $lengthError    = (int) ($percentError   * self::PROGRESSION_LENGTH);

            $lengthLeft = self::PROGRESSION_LENGTH - $lengthPassed - $lengthDone - $lengthError - $lengthWarning;
            $lengthLeft = ($lengthLeft < 0 ) ? 0 : $lengthLeft;

            $msg  = "\r" . sprintf('%3d', round($percent*100, 0)) . "%: [";

            $msg .= '<info>'    . str_repeat('#', $lengthDone)   . '</info>';
            $msg .= '<comment>' . str_repeat('#', $lengthPassed) . '</comment>';
            $msg .= '<err>'   . str_repeat('#', $lengthError)  . '</err>';

            $msg .= str_repeat('-', $lengthLeft) . '] ';
            $msg .= ($this->nbrSuccess)    ? " Done:<info>$this->nbrSuccess</info>" : '';
            $msg .= ($this->nbrWarning) ? " Warn:<comment>$this->nbrWarning</comment>" : '';
            $msg .= ($this->nbrPassed)  ? " Pass:$this->nbrPassed" : '';
            $msg .= ($this->nbrError)   ? " Err:<err>$this->nbrError</err>" : '';
            $msg .= ' Remaining:' . ($nbrRemaining);

            $msg .= ' Elapsed:<info>' . $this->formatTime($timeElapsed) . '</info>';
            $msg .= ' ETA:<comment>' . $this->formatTime($timeETA) . '</comment>';

            $msg .= " \r";

            $this->output->write($msg);

            # Show Progress bar each second
            $this->lastShown = time();
        } catch (\Exception $e) {
            # Do not break your stuff
        }

        return $this;
    }

    /**
     * @param int $time
     * @return string
     */
    private function formatTime(int $time): string
    {
        $sec  = $time % 60;
        $min  = ((int) ($time / 60)) % 60;
        $hour = (int) ($time / 3600);

        return sprintf('%02d:%02d:%02d', $hour, $min, $sec);
    }

    /**
     * @return int
     */
    public function getNbrDone()
    {
        return ($this->nbrPassed + $this->nbrWarning + $this->nbrSuccess + $this->nbrError);
    }

    /**
     * @param bool $doNotReturn0: Do not return 0 to avoid divedByZero
     * @return int
     */
    public function getNbrSuccess($doNotReturn0 = false): int
    {
        if ($doNotReturn0 && empty($this->nbrSuccess)) {
            return 1;
        }

        return $this->nbrSuccess;
    }

    /**
     * @return int
     */
    public function getNbrWarning(): int
    {
        return $this->nbrWarning;
    }

    /**
     * @return int
     */
    public function getNbrError(): int
    {
        return $this->nbrError;
    }

    /**
     * @return int
     */
    public function getNbrPassed(): int
    {
        return $this->nbrPassed;
    }

}
