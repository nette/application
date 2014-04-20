<?php

/**
 * Test: Nette\Application\MessageStorage
 *
 * @author     Martin Major
 */

use Nette\Application\MessageStorage,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/mocks.php';


$session = new MockSession;
$session->mockSection = new MockSessionSection;

$messageStorage = new MessageStorage($session);

Assert::false($messageStorage->hasMessages());

$messageStorage->addMessage('test', 'error');

$id = $messageStorage->getId();

// redirect with id in URL

$session->sectionName = 'Nette.Application.Flash/' . $id;

Assert::true($messageStorage->hasMessages());

$messages = $messageStorage->getMessages($id);
Assert::count(1, $messages);
Assert::equal((object) array('message' => 'test', 'type' => 'error'), $messages[0]);
