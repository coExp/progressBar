<?php

namespace coExp\ProgressBar\Tests;

use coExp\ProgressBar\Exception\MultipleBarConfigurationException;
use coExp\ProgressBar\MultipleBar;
use \Symfony\Component\Console\Helper\ProgressBar as ProgressBar;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class MultipleBarTest extends TestCase
{
    /** @var OutputInterface */
    protected $output;

    public function setUp()
    {
        parent::setUp();

        $this->output = new ConsoleOutput();
    }

    public function test_throw_exception()
    {
        $this->expectException(MultipleBarConfigurationException::class);

        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__)
            ->addProgressBar(-2)
            ->show();

        $mb->erase();
    }

    public function test_one_line()
    {
        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__.': '.(new \DateTime())->format(DATE_ATOM))
            ->addProgressBar();

        $mb->getProgressBarByIndex(0)
            ->setMaxSteps(20);

        $this->assertInstanceOf(ProgressBar::class, $mb->getProgressBarByIndex(0));
        $this->assertNull($mb->getProgressBarByIndex(1));

        for ($i = 0 ; $i < 20 ; $i++) {
            $mb->getProgressBarByIndex(0)->advance();
            $mb->show();
            usleep(100000);
        }

        $mb->erase();
    }

    public function test_two_line()
    {
        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__.': '.(new \DateTime())->format(DATE_ATOM))
            ->addProgressBarByName(['Master']);

        $masterProgressBar = $mb->getProgressBarByName('Master');
        $masterProgressBar->setMaxSteps(4);
        $masterProgressBar->setMessage('Master');

        $this->assertInstanceOf(ProgressBar::class, $mb->getProgressBarByName('Master'));
        $this->assertNull($mb->getProgressBarByName('Child'));

        for ($i = 0 ; $i < 4 ; $i++) {
            $mb->addProgressBarByName(['Child']);

            $this->assertInstanceOf(ProgressBar::class, $mb->getProgressBarByName('Child'));
            $this->assertNull($mb->getProgressBarByName('Child 2'));

            $numberStuff = rand(23, 56);
            $childProgressBar = $mb->getProgressBarByName('Child');
            $childProgressBar->setMaxSteps($numberStuff);
            $childProgressBar->setMessage("Children #$i");

            for ($j = 0; $j < $numberStuff; $j++) {
                $mb->getProgressBarByName('Child')->advance();
                $mb->show();
                usleep(100000);
            }

            $mb->removeProgressBarByName('Child');

            $mb->getProgressBarByName('Master')->advance();
        }

        $mb->erase();
    }
}
