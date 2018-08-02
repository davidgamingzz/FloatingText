<?php

namespace ft\command;

use ft\Loader;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FloatingTextCommand extends PluginCommand {

    /** @var Loader */
    private $plugin;

    /**
     * FloatingTextCommand constructor.
     *
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin) {
        parent::__construct("floatingtext", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["ft"]);
        $this->setDescription("Manage floating text particles");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if((!$sender->isOp() and $sender instanceof Player) or (!$sender->hasPermission("ft.access"))) {
            $sender->sendMessage(TextFormat::RED . "You don't have permission to execute this command!");
            return;
        }
        if(!isset($args[1])) {
            $sender->sendMessage(TextFormat::GRAY . "Commands:");
            $sender->sendMessage(TextFormat::DARK_GRAY . " - " . TextFormat::WHITE . "/floatingtext add <idenifier> <text> <title>");
            $sender->sendMessage(TextFormat::DARK_GRAY . " - " . TextFormat::WHITE . "/floatingtext remove <identifier>");
            return;
        }
        $identifier = $args[1];
        switch($args[0]) {
            case "add":
                if(!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "You may only execute this command in-game");
                    return;
                }
                if(!isset($args[3])) {
                    $sender->sendMessage(TextFormat::GRAY . "Commands:");
                    $sender->sendMessage(TextFormat::DARK_GRAY . " - " . TextFormat::WHITE . "/floatingtext add <identifier> <text> <title>");
                    $sender->sendMessage(TextFormat::DARK_GRAY . " - " . TextFormat::WHITE . "/floatingtext remove <identifier>");
                    return;
                }
                $text = (string)$args[2];
                $title = (string)$args[3];
                if($this->plugin->getFloatingTextById($identifier) !== null) {
                    $sender->sendMessage(TextFormat::RED . "$identifier is an existing identifier!");
                    return;
                }
                $this->plugin->addFloatingText($identifier, $title, $text, $sender);
                $sender->sendMessage(TextFormat::GREEN . "You have added floating text $identifier at {$sender->getFloorX()}, {$sender->getFloorY()}, {$sender->getFloorZ()} in world {$sender->getLevel()->getName()}!");
                return;
                break;
            case "remove":
                if($this->plugin->getFloatingTextById($identifier) === null) {
                    $sender->sendMessage(TextFormat::RED . "$identifier is an invalid identifier!");
                    return;
                }
                $this->plugin->removeFloatingText($identifier);
                $sender->sendMessage(TextFormat::GREEN . "You have removed floating text $identifier!");
                return;
                break;
            default:
                $sender->sendMessage(TextFormat::GRAY . "Commands:");
                $sender->sendMessage(TextFormat::DARK_GRAY . " - " . TextFormat::WHITE . "/floatingtext add <identifier> <text> <title>");
                $sender->sendMessage(TextFormat::DARK_GRAY . " - " . TextFormat::WHITE . "/floatingtext remove <identifier>");
                return;
        }
    }
}