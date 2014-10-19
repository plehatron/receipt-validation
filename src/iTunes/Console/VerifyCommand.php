<?php

namespace Plehatron\ReceiptValidation\iTunes\Console;

use Plehatron\ReceiptValidation\iTunes\Validator;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class VerifyCommand extends Command
{
    protected function configure()
    {
        $this->setName('itunes-receipt')
            ->setDescription('Verify iTunes receipt data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new Question('Please enter app\'s shared secret: ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new RuntimeException('Shared secret must be provided');
            }
            return $answer;
        });
        $question->setHidden(true);
        $question->setMaxAttempts(2);
        $sharedSecret = $helper->ask($input, $output, $question);

        $receiptFile = __DIR__ . '/../../../tmp/receipt';
        if (!is_file(realpath($receiptFile))) {
            throw new RuntimeException(sprintf('Create a file with receipt data here %s', $receiptFile));
        }
        $receiptData = file_get_contents($receiptFile);
        if (empty($receiptData)) {
            throw new RuntimeException(sprintf('Put receipt data here %s', $receiptFile));
        }

        $validator = new Validator();
        $validator->setSecret($sharedSecret);
        $validator->setReceiptData($receiptData);
        $response = $validator->validate();

        var_dump($response);
    }
}