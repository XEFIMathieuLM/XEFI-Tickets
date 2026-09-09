<?php

namespace Tickets\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Tickets\Mcp\Resources\TicketInputRules;
use Tickets\Mcp\Tools\AssignTicketTool;
use Tickets\Mcp\Tools\OpenTicketTool;
use Tickets\Mcp\Tools\SearchTicketsTool;
use Tickets\Mcp\Tools\ShowTicketTool;

#[Name('XEFI Tickets')]
#[Version('1.0.0')]
#[Instructions('Read and open support tickets. Every call acts as the signed-in account and sees only what that account may see. Read the ticket input rules resource before opening one.')]
class TicketsServer extends Server
{
    protected array $tools = [
        SearchTicketsTool::class,
        ShowTicketTool::class,
        OpenTicketTool::class,
        AssignTicketTool::class,
    ];

    protected array $resources = [
        TicketInputRules::class,
    ];

    protected array $prompts = [];
}
