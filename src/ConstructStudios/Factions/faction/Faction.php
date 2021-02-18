<?php
namespace ConstructStudios\Factions\faction;

use pocketmine\level\Position;
use ConstructStudios\Factions\Loader;

class Faction {

    /** @var string[] */
    protected $members = [];

    /** @var string */
    protected $name = "";

    /** @var Member[] */
    protected $membersCache = null;

    /** @var float */
    protected $balance = 0.00;

    /** @var float */
    protected $value = 0.00;

    /** @var float */
    protected $str = 0.00;

    /** @var int */
    protected $dtr = 10;

    /** @var string */
    protected $leader = "";

    /** @var int */
    protected $guardians = 0;

    /** @var array[] */
    protected $claims = [];

    /** @var Faction|null */
    protected $warAgainst = null;

    /** @var string[] */
    protected $allies = [];

    /** @var string[] */
    protected $enemies = [];

    /** @var Faction[] */
    protected $alliesCache = null;

    /** @var Faction[] */
    protected $enemiesCache = null;

    /** @var bool */
    protected $isOpen = false;

    /** @var string */
    protected $description = "No description set";

    /** @var Position|null */
    protected $home;

    /**
     * @param string $leader
     * @param string $name
     * @param string $description
     * @param array $properties
     * @return Faction
     */
    public static function new(string $leader, string $name, string $description, array $properties = []): self {
        $fac = new self;
        $fac->name = $name;
        $fac->leader = $leader;
        $fac->description = $description;

        if($properties !== []){
            $fac->loadData($properties);
        }

        return $fac;
    }

    /**
     * @return array
     */
    public function getData(): array {
        $homeData = [
            0,
            0,
            0,
            "none"
        ];
        if($this->home !== null){
            $homeData = [
                $this->home->x,
                $this->home->y,
                $this->home->z,
                $this->home->getLevel()->getName()
            ];
        }

        return [
            "members" => $this->members,
            "balance" => $this->balance,
            "leader" => $this->leader,
            "name" => $this->name,
            "guardians" => $this->guardians,
            "claims" => $this->claims,
            "allies" => $this->allies,
            "value" => $this->value,
            "dtr" => $this->dtr,
            "str" => $this->str,
            "home" => $homeData,
            "isOpen" => $this->isOpen
        ];
    }

    /**
     * @param array $data
     */
    public function loadData(array $data): void {
        $this->members = $data["members"] ?? [];
        $this->balance = $data["balance"] ?? 0.00;
        $this->leader = $data["leader"];
        $this->name = $data["name"];
        $this->guardians = $data["guardians"] ?? 0;
        $this->claims = $data["claims"] ?? [];
        $this->allies = $data["allies"] ?? [];
        $this->value = $data["value"] ?? 0.00;
        $this->str = $data["str"] ?? 0;
        $this->dtr = $data["dtr"] ?? 0;
        $this->isOpen = false; // $data["isOpen"] ?? true;

        $homeData = $data["home"];
        if(Loader::getInstance()->getServer()->getLevelByName($homeData[3]) == null){
            Loader::getInstance()->getServer()->loadLevel($homeData[3]);
        }
        if(($level = Loader::getInstance()->getServer()->getLevelByName($homeData[3])) !== null){
            $this->home = new Position($homeData[0], $homeData[1], $homeData[2], $level);
        }
    }


    /**
     * @return null|Position
     */
    public function getHome(): ?Position {
        return $this->home;
    }

    /**
     * @param null|Position $home
     */
    public function setHome(?Position $home): void {
        $this->home = $home;
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool {
        return $this->isOpen;
    }

    /**
     * @param bool $isOpen
     */
    public function setOpen(bool $isOpen): void {
        $this->isOpen = $isOpen;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return Faction[]
     */
    public function getAllies(): array {
        if($this->alliesCache === null){
            $this->alliesCache = [];

            foreach($this->allies as $key => $ally){
                $ally = Loader::getInstance()->getFaction($ally);

                if($ally !== null){
                    $this->alliesCache[] = $ally;
                }else{
                    unset($this->allies[$key]);
                }
            }
        }

        return $this->alliesCache;
    }

    /**
     * @return Faction[]
     */
    public function getEnemies(): array {
        if($this->enemiesCache === null){
            $this->enemiesCache = [];

            foreach($this->enemies as $key => $enemy){
                $enemy = Loader::getInstance()->getFaction($enemy);

                if($enemy !== null){
                    $this->enemiesCache[] = $enemy;
                }else{
                    unset($this->enemies[$key]);
                }
            }
        }

        return $this->enemiesCache;
    }

    /**
     * @return float
     */
    public function getSTR(): float {
        return $this->str;
    }

    /**
     * @param float $str
     */
    public function addSTR(float $str): void {
        $this->str += $str;
    }

    /**
     * @param float $str
     */
    public function removeSTR(float $str): void {
        $this->str -= $str;
    }

    /**
     * @return int
     */
    public function getDTR(): int {
        return $this->dtr;
    }

    /**
     * @param int $dtr
     */
    public function addDTR(int $dtr): void {
        $this->dtr += $dtr;
    }

    /**
     * @param int $dtr
     */
    public function removeDTR(int $dtr): void {
        $this->dtr -= $dtr;
    }

    /**
     * @return float
     */
    public function getBalance(): float {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function addBalance(float $balance): void {
        $this->balance += $balance;
    }

    /**
     * @param float $balance
     */
    public function removeBalance(float $balance): void {
        $this->balance -= $balance;
    }

    /**
     * @return float
     */
    public function getValue(): float {
        return $this->value;
    }

    /**
     * @param float $v
     */
    public function addValue(float $v): void {
        $this->value += $v;
    }

    /**
     * @param float $v
     */
    public function removeValue(float $v): void {
        $this->value -= $v;
    }

    /**
     * @param float $value
     */
    public function setValue(float $value): void {
        $this->value = $value;
    }

    /**
     * @return array[]
     */
    public function getClaims(): array {
        return $this->claims;
    }

    /**
     * @return int
     */
    public function getGuardians(): int {
        return $this->guardians;
    }

    /**
     * @return Member
     */
    public function getLeader(): Member {
        return Loader::getInstance()->getMemberByName($this->leader);
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array {
        if($this->membersCache === null){
            $this->membersCache = [];

            foreach($this->members as $member){
                $this->membersCache[] = Loader::getInstance()->getMemberByName($member);
            }
        }

        return $this->membersCache;
    }

    /**
     * @param Member $member
     * @return bool
     */
    public function isMember(Member $member): bool {
        return in_array($member, $this->getMembers()) or $member === $this->getLeader();
    }

    /**
     * @param Member $member
     */
    public function addMember(Member $member): void {
        $this->members[] = $member->getName();
        $this->membersCache[] = $member;
    }

    /**
     * @param Member $member
     */
    public function removeMember(Member $member): void {
        unset($this->members[array_search($member->getName(), $this->members)]);
        unset($this->membersCache[array_search($member, $this->getMembers())]);
    }

    /**
     * @return bool
     */
    public function isFull(): bool {
        return count($this->members) >= Loader::getInstance()->getConfig()->get("max-members");
    }

    /**
     * @return null|Faction
     */
    public function getWarAgainst(): ?Faction {
        return $this->warAgainst;
    }

    /**
     * @param null|Faction $faction
     * @return bool
     */
    public function isAlly(?Faction $faction): bool {
        return in_array($faction, $this->getAllies());
    }

    /**
     * @param null|Faction $faction
     * @return bool
     */
    public function isEnemy(?Faction $faction): bool {
        return in_array($faction, $this->getEnemies());
    }

    /**
     * @param Faction $faction
     */
    public function addAlly(Faction $faction): void {
        $this->allies[] = $faction->getName();
        $this->alliesCache[] = $faction;
    }

    /**
     * @param Faction $faction
     */
    public function addEnemy(Faction $faction): void {
        $this->enemies[] = $faction->getName();
        $this->enemiesCache[] = $faction;
    }

    /**
     * @param Faction $faction
     */
    public function removeAlly(Faction $faction): void {
        unset($this->allies[array_search($faction->getName(), $this->allies)]);
        unset($this->alliesCache[array_search($faction, $this->getAllies())]);
    }

    /**
     * @param Faction $faction
     */
    public function removeEnemy(Faction $faction): void {
        unset($this->enemies[array_search($faction->getName(), $this->allies)]);
        unset($this->enemiesCache[array_search($faction, $this->getAllies())]);
    }

    /**
     * @param string[] $allies
     */
    public function setAllies(array $allies): void {
        $this->allies = $allies;
        $this->alliesCache = null;
    }

    /**
     * @param string[] $enemies
     */
    public function setEnemies(array $enemies): void {
        $this->enemies = $enemies;
        $this->enemiesCache = null;
    }

    /**
     * @param Faction[]|null $alliesCache
     */
    public function setAlliesCache(?array $alliesCache): void {
        $this->alliesCache = $alliesCache;
    }

    /**
     * @param float $balance
     */
    public function setBalance(float $balance): void {
        $this->balance = $balance;
    }

    /**
     * @param array[] $claims
     */
    public function setClaims(array $claims): void {
        $this->claims = $claims;
    }

    /**
     * @param int $x
     * @param int $z
     * @param string $level
     */
    public function addClaim(int $x, int $z, string $level): void {
        $this->claims[] = [
            $x,
            $z,
            $level
        ];
    }

    /**
     * @param int $x
     * @param int $z
     * @param string $level
     */
    public function removeClaim(int $x, int $z, string $level): void {
        unset($this->claims[array_search([
                $x,
                $z,
                $level
            ], $this->claims)]);
    }

    /**
     * @param int $x
     * @param int $z
     * @param string $level
     * @return bool
     */
    public function isClaimed(int $x, int $z, string $level): bool {
        return in_array([
            $x,
            $z,
            $level
        ], $this->claims);
    }

    /**
     * @param int $guardians
     */
    public function setGuardians(int $guardians): void {
        $this->guardians = $guardians;
    }

    /**
     * @param string $leader
     */
    public function setLeader(string $leader): void {
        $this->leader = $leader;
    }

    /**
     * @param string[] $members
     */
    public function setMembers(array $members): void {
        $this->members = $members;
        $this->membersCache = null;
    }

    /**
     * @param Member[]|null $membersCache
     */
    public function setMembersCache(?array $membersCache): void {
        $this->membersCache = $membersCache;
    }

    /**
     * @param null|Faction $warAgainst
     */
    public function setWarAgainst(?Faction $warAgainst): void {
        $this->warAgainst = $warAgainst;
    }

    /**
     * @param string $message
     */
    public function broadcastMessage(string $message): void {
        $this->executeForOnline("sendMessage", $message);
    }

    /**
     * @param string $message
     */
    public function broadcastTip(string $message): void {
        $this->executeForOnline("sendTip", $message);
    }

    /**
     * @param string $message
     */
    public function broadcastPopup(string $message): void {
        $this->executeForOnline("sendPopup", $message);
    }

    /**
     * @param string $title
     * @param string $sub
     * @param int $fadeIn
     * @param int $stay
     * @param int $fadeOut
     */
    public function broadcastTitle(string $title, string $sub, int $fadeIn, int $stay, int $fadeOut): void {
        $this->executeForOnline("addTitle", ...[
            $title,
            $sub,
            $fadeIn,
            $stay,
            $fadeOut
        ]);
    }

    /**
     * @param string $func
     * @param mixed ...$args
     */
    public function executeForOnline(string $func, ...$args): void {
        /** @var Member[] $members */
        $members = $this->getMembers();
        $members[] = $this->getLeader();

        foreach($members as $member){
            if($member->getPlayer()->isOnline()){
                $member->getPlayer()->getPlayer()->$func(...$args);
            }
        }
    }
}