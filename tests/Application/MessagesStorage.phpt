<?php

/**
 * Test: Nette\Application\MessagesStorage
 *
 * @author     Martin Major
 */

use Nette\Application\MessagesStorage,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/mocks.php';


$session = new MockSession;
$session->mockSection = new MockSessionSection;

$messageStorage = new MessagesStorage($session);

Assert::false($messageStorage->isOpened());

$messageStorage->addMessage('test', 'error');

$id = $messageStorage->getId();

// redirect with id in URL

$session->sectionName = 'Nette.Application.Flash/' . $id;

Assert::true($messageStorage->isOpened());

$messages = $messageStorage->getMessages($id);
Assert::count(1, $messages);
Assert::equal((object) array('message' => 'test', 'type' => 'error'), $messages[0]);
