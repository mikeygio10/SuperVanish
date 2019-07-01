<?php

namespace HimmelKreis4865\Supervanish;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class vanish extends PluginBase implements Listener{
    public function onEnable(){
        $this->getLogger()->info("Plugin Supervanish wurde geladen");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool{
        $cfg = new Config($this->getDataFolder(). $sender->getName() . ".yml", Config::YAML);
        $online = $this->getServer()->getOnlinePlayers();
        if ($cmd->getName() == "sv"){
            if ($sender->hasPermission("sv.use") or $sender->hasPermission("sv.admin")){
                if (isset($args[0])){
                    $target = $this->getServer()->getPlayer($args[0]);
                    
                    if (!$target == null){
                        $tcfg = new Config($this->getDataFolder(). $target . ".yml", Config::YAML);
                        if (file_exists($this->getDataFolder(). $target . ".yml")){
                            if ($tcfg->get("Vanished") == true){
                                $tcfg->set("Vanished", false);
                                $tcfg->save();
                                $target->sendMessage($this->getConfig()->get("DeactivateByOtherMessage"));
                                $sender->sendMessage($this->getConfig()->get("DeactivateOtherMessage"));
                                foreach ($online as $p){
                                    $p->showPlayer($target);
                                }
                            }else{
                                $tcfg->set("Vanished", true);
                                $tcfg->save();
                                $target->sendMessage($this->getConfig()->get("ActivateByOtherMessage"));
                                $sender->sendMessage($this->getConfig()->get("ActivateOtherMessage"));
                                    if (!$p->isOp() or !$p->hasPermission("sv.see")){
                                        $p->hidePlayer($target);
                                    }else{
                                        $p->showPlayer($target);
                                    }
                                }
                            }
                        }else{
                            $sender->sendMessage($this->getConfig()->get("InvalidPlayer"));
                        }
                    }
                }else{
                    if ($cfg->get("Vanished") == true){
                        foreach ($online as $p){
                            $p->showPlayer($sender);
                        }
                        $sender->sendMessage($this->getConfig()->get("DeactivateMessage"));
                        $cfg->set("Vanished", false);
                    }else{
                        foreach ($online as $p){
                            if (!$p->isOp() or !$p->hasPermission("hide.see")){
                                $p->showPlayer($sender);
                            }
                        }
                        $sender->sendMessage($this->getConfig()->get("ActivateMessage"));
                        $cfg->set("Vanished", true);
                    }
                }
            }
        }
        return true;
    }
    public function onJoin(PlayerJoinEvent $event){
        $online = $this->getServer()->getOnlinePlayers();
        $player = $event->getPlayer();
        foreach($online as $p){
            $cfg = new Config($this->getDataFolder(). $p->getName() . ".yml", Config::YAML);
            if (file_exists($this->getDataFolder(). $p->getName() . ".yml")){
                if ($cfg->get("Vanished") == "true"){
                    if (!$player->isOp() or !$player->hasPermission("sv.admin")){
                        $player->hidePlayer($p);
                    }else{
                        $player->showPlayer($p);
                    }
                }
            }
        }
    }
}
