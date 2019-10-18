<?php

namespace coExp\ProgressBar;

use coExp\ProgressBar\Exception\MultipleBarConfigurationException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Helper\ProgressBar as ProgressBar;
use Symfony\Component\Console\Output\StreamOutput;

class MultipleBar
{
    const CLEAR_LINE = "\e[2K\r";
    const MOVE_CURSOR_UP = "\e[1A";

    protected $title;

    /** @var ProgressBar[] */
    protected $progressBars = [];

    /** @var OutputInterface */
    protected $originalOutput;

    /** @var StreamOutput */
    protected $stdOutput;

    /** @var bool */
    protected $isStdError = false;

    private $length = 0;

    /**
     * Time of begin of command
     * @var int|null
     */
    protected $time = null;

    /**
     * ProgressBar constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        // Force writing on StdOut
        $this->stdOutput = new StreamOutput($output->getStream());

        // By default, Symfony/Helper write on stdError
        $this->originalOutput = $output;
        $this->time = time();
    }

    /**
     * @return bool
     */
    public function isStdError(): bool
    {
        return $this->isStdError;
    }

    /**
     * @param bool $stdError
     * @return MultipleBar
     */
    public function setStdError(bool $stdError): self
    {
        $this->isStdError = $stdError;

        return $this;
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return $this
     */
    public function erase()
    {
        for ($i = 0 ; $i < $this->length; $i++) {
            $this->originalOutput->write(self::CLEAR_LINE.self::MOVE_CURSOR_UP);
        }

        $this->length = 0;

        return $this;
    }

    /**
     * @return $this
     */
    public function show()
    {
        $this->erase();

        if (false === empty($this->title)) {
            $this->originalOutput->writeln(self::CLEAR_LINE.$this->title);
            $this->length++;
        }

        foreach ($this->progressBars as $progressBar) {
            $progressBar->display();
            $this->originalOutput->write("\n");
            $this->length++;
        }

        return $this;
    }

    /**
     * @param int $index
     * @return $this
     */
    public function removeProgressBarByIndex(int $index)
    {
        unset($this->progressBars[$index]);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeProgressBarByName(string $name)
    {
        unset($this->progressBars[$name]);

        return $this;
    }

    /**
     * @return OutputInterface|StreamOutput
     */
    protected function getOutput()
    {
        if ($this->isStdError()) {
            return $this->originalOutput;
        }

        return $this->stdOutput;
    }

    /**
     * @param int $number
     * @return MultipleBar
     * @throws MultipleBarConfigurationException
     */
    public function addProgressBar(int $number = 1)
    {
        if ($number < 1) {
            throw new MultipleBarConfigurationException('Illegal number of ProgressBar');
        }

        for ($i = 0 ; $i < $number ; $i++) {
           $this->progressBars[] = new ProgressBar($this->getOutput());
        }

        return $this;
    }

    /**
     * @param string[] $names
     * @return MultipleBar
     */
    public function addProgressBarByName(array $names)
    {
        foreach ($names as $name) {
           $this->progressBars[$name] = new ProgressBar($this->getOutput());
        }

        return $this;
    }

    /**
     * @param int $index
     * @return ProgressBar|null
     */
    public function getProgressBarByIndex(int $index)
    {
        return $this->progressBars[$index] ?? null;
    }

    /**
     * @param string $name
     * @return ProgressBar|null
     */
    public function getProgressBarByName(string $name)
    {
        return $this->progressBars[$name] ?? null;
    }
}
