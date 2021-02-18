<?php
namespace ConstructStudios\Factions\faction;

use pocketmine\IPlayer;
use pocketmine\Server;
use ConstructStudios\Factions\Loader;

class Member {

    public const MEMBER = "*";

    public const RECRUITMENT = "+";

    public const OFFICER = "*";

    public const LEADER = "***";

    /** @var string */
    protected $name = "";

    /** @var string */
    protected $rank = self::RECRUITMENT;

    /** @var float */
    protected $activityPoints = 0.00;

    /** @var string */
    protected $faction = "";

    /** @var bool */
    protected $canBuild = false;

    /** @var bool */
    protected $canTouch = true;

    /** @var bool */
    protected $canInviteMember = false;

    /** @var bool */
    protected $canKickMember = false;

    /** @var bool */
    protected $canOpenFaction = false;

    /** @var bool */
    protected $canCloseFaction = false;

    /** @var bool */
    protected $canManagePermissions = false;

    /** @var bool */
    protected $canManageRanks = false;

    /** @var bool */
    protected $canSpawnGuardian = false;

    /** @var bool */
    protected $canClaimLands = false;

    /** @var bool */
    protected $canDeclaimLands = false;

    /** @var bool */
    protected $canWithdrawMoney = false;

    /** @var bool */
    protected $canAllyFaction = false;

    /** @var bool */
    protected $canEnemyFaction = false;

    /** @var bool */
    protected $isFactionChatOn = false;

    /** @var bool */
    protected $isHudOn = true;

    /**
     * @return bool
     */
    public function isHudOn(): bool {
        return $this->isHudOn;
    }

    /**
     * @param bool $isHudOn
     */
    public function setIsHudOn(bool $isHudOn): void {
        $this->isHudOn = $isHudOn;
    }

    /**
     * @return bool
     */
    public function isCanManageRanks(): bool {
        return $this->canManageRanks;
    }

    /**
     * @param bool $canManageRanks
     */
    public function setCanManageRanks(bool $canManageRanks): void {
        $this->canManageRanks = $canManageRanks;
    }

    /**
     * @return bool
     */
    public function isFactionChatOn(): bool {
        return $this->isFactionChatOn;
    }

    /**
     * @param bool $isFactionChatOn
     */
    public function setFactionChatOn(bool $isFactionChatOn): void {
        $this->isFactionChatOn = $isFactionChatOn;
    }

    /**
     * @param string $rank
     * @return int
     */
    public static function getRankPriority(string $rank): int {
        switch($rank){
            case self::RECRUITMENT:
                return 0;
            case self::MEMBER:
                return 1;
            case self::OFFICER:
                return 2;
            case self::LEADER:
                return 3;
            default:
                return 0;
        }
    }

    /**
     * @return array
     */
    public function getData(): array {
        return [
            "rank" => $this->rank,
            "name" => $this->name,
            "activityPoints" => $this->activityPoints,
            "faction" => $this->getFaction() ? $this->getFaction()->getName() : "",
            "canTouch" => $this->canTouch,
            "canBuild" => $this->canBuild,
            "canInviteMember" => $this->canInviteMember,
            "canKickMember" => $this->canKickMember,
            "canOpenFaction" => $this->canOpenFaction,
            "canCloseFaction" => $this->canCloseFaction,
            "canManagePermissions" => $this->canManagePermissions,
            "canSpawnGuardian" => $this->canSpawnGuardian,
            "canClaimLands" => $this->canClaimLands,
            "canDeclaimLands" => $this->canDeclaimLands,
            "canWithdrawMoney" => $this->canWithdrawMoney,
            "canAllyFaction" => $this->canAllyFaction,
            "canEnemyFaction" => $this->canEnemyFaction,
            "canManageRanks" => $this->canManageRanks
        ];
    }

    /**
     * @param array $data
     */
    public function loadData(array $data): void {
        $this->rank = $data["rank"] ?? self::MEMBER;
        $this->name = $data["name"];
        $this->activityPoints = $data["activityPoints"] ?? 0.00;
        $this->faction = $data["faction"] ?? "";

        $this->canTouch = $data["canTouch"] ?? true;
        $this->canBuild = $data["canBuild"] ?? false;
        $this->canInviteMember = $data["canInviteMember"] ?? false;
        $this->canKickMember = $data["canKickMember"] ?? false;
        $this->canOpenFaction = $data["canOpenFaction"] ?? false;
        $this->canCloseFaction = $data["canCloseFaction"] ?? false;
        $this->canManagePermissions = $data["canManagePermissions"] ?? false;
        $this->canSpawnGuardian = $data["canSpawnGuardian"] ?? false;
        $this->canClaimLands = $data["canClaimLands"] ?? false;
        $this->canDeclaimLands = $data["canDeclaimLands"] ?? false;
        $this->canWithdrawMoney = $data["canWithdrawMoney"] ?? false;
        $this->canAllyFaction = $data["canAllyFaction"] ?? false;
        $this->canEnemyFaction = $data["canEnemyFaction"] ?? false;
        $this->canManageRanks = $data["canManageRanks"] ?? false;
    }

    public function resetPermissions(): void {
        $this->canTouch = true;
        $this->canBuild = false;
        $this->canInviteMember = false;
        $this->canKickMember = false;
        $this->canOpenFaction = false;
        $this->canCloseFaction = false;
        $this->canManagePermissions = false;
        $this->canSpawnGuardian = false;
        $this->canClaimLands = false;
        $this->canDeclaimLands = false;
        $this->canWithdrawMoney = false;
        $this->canAllyFaction = false;
        $this->canEnemyFaction = false;
        $this->canManageRanks = false;
    }

    public function setMemberPermissions(): void {
        $this->canTouch = true;
        $this->canBuild = true;
        $this->canInviteMember = false;
        $this->canKickMember = false;
        $this->canOpenFaction = false;
        $this->canCloseFaction = false;
        $this->canManagePermissions = false;
        $this->canSpawnGuardian = false;
        $this->canClaimLands = false;
        $this->canDeclaimLands = false;
        $this->canWithdrawMoney = false;
        $this->canAllyFaction = false;
        $this->canEnemyFaction = false;
        $this->canManageRanks = false;
    }

    public function setOfficerPermissions(): void {
        $this->canTouch = true;
        $this->canBuild = true;
        $this->canInviteMember = true;
        $this->canKickMember = true;
        $this->canOpenFaction = true;
        $this->canCloseFaction = true;
        $this->canManagePermissions = false;
        $this->canSpawnGuardian = false;
        $this->canClaimLands = true;
        $this->canDeclaimLands = true;
        $this->canWithdrawMoney = false;
        $this->canAllyFaction = false;
        $this->canEnemyFaction = false;
        $this->canManageRanks = false;
    }

    public function grantAllPermissions(): void {
        $this->canTouch = true;
        $this->canBuild = true;
        $this->canInviteMember = true;
        $this->canKickMember = true;
        $this->canOpenFaction = true;
        $this->canCloseFaction = true;
        $this->canManagePermissions = true;
        $this->canSpawnGuardian = true;
        $this->canClaimLands = true;
        $this->canDeclaimLands = true;
        $this->canWithdrawMoney = true;
        $this->canAllyFaction = true;
        $this->canEnemyFaction = true;
        $this->canManageRanks = true;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return IPlayer
     */
    public function getPlayer(): IPlayer {
        return Server::getInstance()->getOfflinePlayer($this->name);
    }

    /**
     * @return float
     */
    public function getActivityPoints(): float {
        return $this->activityPoints;
    }

    /**
     * @param float $activityPoints
     */
    public function setActivityPoints(float $activityPoints): void {
        $this->activityPoints = $activityPoints;
    }

    /**
     * @param float $points
     */
    public function addActivityPoints(float $points): void {
        $this->activityPoints += $points;
    }

    /**
     * @param float $points
     */
    public function removeActivityPoints(float $points): void {
        $this->activityPoints -= $points;
    }

    /**
     * @return Faction|null
     */
    public function getFaction(): ?Faction {
        return Loader::getInstance()->getFaction($this->faction);
    }

    /**
     * @param string $faction
     */
    public function setFaction(string $faction): void {
        $this->faction = $faction;
    }

    /**
     * @return bool
     */
    public function isCanAllyFaction(): bool {
        return $this->canAllyFaction;
    }

    /**
     * @return bool
     */
    public function isCanBuild(): bool {
        return $this->canBuild;
    }

    /**
     * @return bool
     */
    public function isCanClaimLands(): bool {
        return $this->canClaimLands;
    }

    /**
     * @return bool
     */
    public function isCanCloseFaction(): bool {
        return $this->canCloseFaction;
    }

    /**
     * @return bool
     */
    public function isCanDeclaimLands(): bool {
        return $this->canDeclaimLands;
    }

    /**
     * @return bool
     */
    public function isCanEnemyFaction(): bool {
        return $this->canEnemyFaction;
    }

    /**
     * @return bool
     */
    public function isCanInviteMember(): bool {
        return $this->canInviteMember;
    }

    /**
     * @return string
     */
    public function getRank(): string {
        return $this->rank;
    }

    /**
     * @param string $rank
     */
    public function setRank(string $rank): void {
        $this->rank = $rank;
    }

    /**
     * @return bool
     */
    public function isCanKickMember(): bool {
        return $this->canKickMember;
    }

    /**
     * @return bool
     */
    public function isCanManagePermissions(): bool {
        return $this->canManagePermissions;
    }

    /**
     * @return bool
     */
    public function isCanOpenFaction(): bool {
        return $this->canOpenFaction;
    }

    /**
     * @return bool
     */
    public function isCanSpawnGuardian(): bool {
        return $this->canSpawnGuardian;
    }

    /**
     * @return bool
     */
    public function isCanTouch(): bool {
        return $this->canTouch;
    }

    /**
     * @return bool
     */
    public function isCanWithdrawMoney(): bool {
        return $this->canWithdrawMoney;
    }

    /**
     * @param bool $canAllyFaction
     */
    public function setCanAllyFaction(bool $canAllyFaction): void {
        $this->canAllyFaction = $canAllyFaction;
    }

    /**
     * @param bool $canManagePermissions
     */
    public function setCanManagePermissions(bool $canManagePermissions): void {
        $this->canManagePermissions = $canManagePermissions;
    }

    /**
     * @param bool $canBuild
     */
    public function setCanBuild(bool $canBuild): void {
        $this->canBuild = $canBuild;
    }

    /**
     * @param bool $canClaimLands
     */
    public function setCanClaimLands(bool $canClaimLands): void {
        $this->canClaimLands = $canClaimLands;
    }

    /**
     * @param bool $canCloseFaction
     */
    public function setCanCloseFaction(bool $canCloseFaction): void {
        $this->canCloseFaction = $canCloseFaction;
    }

    /**
     * @param bool $canDeclaimLands
     */
    public function setCanDeclaimLands(bool $canDeclaimLands): void {
        $this->canDeclaimLands = $canDeclaimLands;
    }

    /**
     * @param bool $canEnemyFaction
     */
    public function setCanEnemyFaction(bool $canEnemyFaction): void {
        $this->canEnemyFaction = $canEnemyFaction;
    }

    /**
     * @param bool $canInviteMember
     */
    public function setCanInviteMember(bool $canInviteMember): void {
        $this->canInviteMember = $canInviteMember;
    }

    /**
     * @param bool $canKickMember
     */
    public function setCanKickMember(bool $canKickMember): void {
        $this->canKickMember = $canKickMember;
    }

    /**
     * @param bool $canOpenFaction
     */
    public function setCanOpenFaction(bool $canOpenFaction): void {
        $this->canOpenFaction = $canOpenFaction;
    }

    /**
     * @param bool $canSpawnGuardian
     */
    public function setCanSpawnGuardian(bool $canSpawnGuardian): void {
        $this->canSpawnGuardian = $canSpawnGuardian;
    }

    /**
     * @param bool $canTouch
     */
    public function setCanTouch(bool $canTouch): void {
        $this->canTouch = $canTouch;
    }

    /**
     * @param bool $canWithdrawMoney
     */
    public function setCanWithdrawMoney(bool $canWithdrawMoney): void {
        $this->canWithdrawMoney = $canWithdrawMoney;
    }
}