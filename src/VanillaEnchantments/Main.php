<?php

namespace VanillaEnchantments;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\EnchantmentInstance;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->getLogger()->info("VanillaEnchantments enabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if (!$sender->hasPermission("enchant.use")) {
            $sender->sendMessage("§cYou do not have permission.");
            return true;
        }

        if (count($args) < 3) {
            $sender->sendMessage("§eUsage: §a/enchant <player> <enchantment> <level>");
            return true;
        }

        // Find player (partial name support)
        $target = $this->getServer()->getPlayerByPrefix($args[0]);

        if (!$target instanceof Player) {
            $sender->sendMessage("§cPlayer not found.");
            return true;
        }

        $enchantName = strtolower($args[1]);
        $level = (int)$args[2];

        if ($level < 1) {
            $sender->sendMessage("§cLevel must be at least 1.");
            return true;
        }

        if ($level > 32000) {
            $level = 32000;
        }

        $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantName);

        if ($enchantment === null) {
            $sender->sendMessage("§cInvalid enchantment.");
            return true;
        }

        $item = $target->getInventory()->getItemInHand();

        if ($item->isNull()) {
            $sender->sendMessage("§cTarget is not holding an item.");
            return true;
        }

        // Apply enchant ignoring normal level cap
        $instance = new EnchantmentInstance($enchantment, $level);
        $item->addEnchantment($instance);

        $target->getInventory()->setItemInHand($item);

        $sender->sendMessage("§aEnchantment applied successfully.");
        if ($sender !== $target) {
            $target->sendMessage("§aYour item has been enchanted.");
        }

        return true;
    }
}
