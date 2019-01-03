<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\DidSaveTextDocumentParams;
use LanguageServerProtocol\ServerCapabilities;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\TextDocumentSyncKind;
use LanguageServerProtocol\TextDocumentSyncOptions;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;
use Phpactor\LanguageServer\Core\Session\Workspace;

final class TextDocumentHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function methods(): array
    {
        return [
            'textDocument/didOpen' => 'didOpen',
            'textDocument/didChange' => 'didChange',
            'textDocument/didClose' => 'didClose',
            'textDocument/didSave' => 'didSave',
            'textDocument/willSave' => 'willSave',
            'textDocument/willSaveWaitUntil' => 'willSaveWaitUntil',
        ];
    }

    public function didOpen(TextDocumentItem $textDocument)
    {
        $this->workspace->open($textDocument);
    }

    public function didChange(VersionedTextDocumentIdentifier $textDocument, array $contentChanges)
    {
        foreach ($contentChanges as $contentChange) {
            $this->workspace->update(
                $textDocument,
                $contentChange['text']
            );
        }
    }

    public function didClose(TextDocumentIdentifier $textDocument)
     {
        $this->workspace->remove(
            $textDocument
        );
    }

    public function didSave(TextDocumentIdentifier $textDocument, string $text = null)
    {
        if ($text !== null) {
            $this->workspace->get($textDocument->uri)->text = $text;
        }
    }

    public function willSave(TextDocumentIdentifier $identifier, int $reason)
    {
    }

    public function willSaveWaitUntil(TextDocumentIdentifier $identifier, int $reason)
    {
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities)
    {
        $options = new TextDocumentSyncOptions();
        $options->change = TextDocumentSyncKind::FULL;

        $capabilities->textDocumentSync = $options;
    }
}
