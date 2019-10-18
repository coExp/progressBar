<?php

namespace coExp\wUnderBar\Tests;

use coExp\wUnderBar\Exception\MultipleBarConfigurationException;
use coExp\wUnderBar\MultipleBar;
use DateTime;
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

    /**
     * @throws MultipleBarConfigurationException
     */
    public function test_throw_exception()
    {
        $this->expectException(MultipleBarConfigurationException::class);

        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__)
            ->addProgressBar(-2)
            ->show();

        $mb->erase();
    }

    /**
     * @throws MultipleBarConfigurationException
     */
    public function test_one_line()
    {
        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__.': '.(new DateTime())->format(DATE_ATOM))
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

    /**
     * @throws MultipleBarConfigurationException
     */
    public function test_one_line_on_stderror()
    {
        $mb = (new MultipleBar($this->output))
            ->setStdError(true)
            ->setTitle(__METHOD__.': '.(new DateTime())->format(DATE_ATOM))
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

    public function test_two_line_unknown_length()
    {
        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__.': '.(new DateTime())->format(DATE_ATOM))
            ->addProgressBarByName(['Master']);

        /** @see https://symfony.com/doc/current/components/console/helpers/progressbar.html#custom-formats */
        ProgressBar::setFormatDefinition('custom', '%message%: %percent%% %current%/%max% |%bar%');

        $masterProgressBar = $mb->getProgressBarByName('Master');
        $masterProgressBar->setMaxSteps(4);
        $masterProgressBar->setMessage('Master');
        $masterProgressBar->setFormat('custom');

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
            $childProgressBar->setFormat('custom');

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

    public function test_two_line_known_length()
    {
        $length = [23, 24, 25, 26]; // total = 98

        $mb = (new MultipleBar($this->output))
            ->setTitle(__METHOD__.': '.(new DateTime())->format(DATE_ATOM))
            ->addProgressBarByName(['Master', 'Child']);

        /** @see https://symfony.com/doc/current/components/console/helpers/progressbar.html#custom-formats */
        ProgressBar::setFormatDefinition('custom', '%message%: %percent%% %current%/%max% |%bar%');

        $masterProgressBar = $mb->getProgressBarByName('Master');
        $masterProgressBar->setMaxSteps(98);
        $masterProgressBar->setMessage('Master');
        $masterProgressBar->setFormat('custom');

        $this->assertInstanceOf(ProgressBar::class, $mb->getProgressBarByName('Master'));
        $this->assertInstanceOf(ProgressBar::class, $mb->getProgressBarByName('Child'));

        foreach ($length as $i => $numberStuff) {
            $childProgressBar = $mb->getProgressBarByName('Child');
            $childProgressBar->setProgress(0);
            $childProgressBar->setMaxSteps($numberStuff);
            $childProgressBar->setMessage("Children #$i");
            $childProgressBar->setFormat('custom');

            for ($j = 0; $j < $numberStuff; $j++) {
                $mb->getProgressBarByName('Child')->advance();
                $mb->getProgressBarByName('Master')->advance();
                $mb->show();
                usleep(100000);
            }
        }

        $mb->erase();
    }
}
