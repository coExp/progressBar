<?php

namespace coExp\ProgressBar;

use coExp\ProgressBar\Exception\MultipleBarConfigurationException;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Helper\ProgressBar as ProgressBar;

class MultipleBar
{
    const CLEAR_LINE = "\e[2K\r";
    const MOVE_CURSOR_UP = "\e[1A";

    protected $title;

    /** @var ProgressBar[] */
    protected $progressBars = [];

    /** @var OutputInterface */
    protected $output;

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
        $this->output = $output;
        $this->time = time();
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
            $this->output->write(self::CLEAR_LINE.self::MOVE_CURSOR_UP);
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
            $this->output->writeln(self::CLEAR_LINE.$this->title);
            $this->length++;
        }

        foreach ($this->progressBars as $progressBar) {
            $progressBar->display();
            $this->output->write("\n");
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
           $this->progressBars[] = new ProgressBar($this->output);
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
           $this->progressBars[$name] = new ProgressBar($this->output);
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
