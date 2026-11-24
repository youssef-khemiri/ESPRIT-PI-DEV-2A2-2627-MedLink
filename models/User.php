<?php
/**
 * models/User.php — Entité User (même style que les modèles Member / Subscription du PDF)
 * type : 'normal' (par défaut, inscription publique) ou 'admin' (uniquement via le Back Office)
 */
class User
{
    public const TYPE_NORMAL = 'normal';
    public const TYPE_ADMIN  = 'admin';

    private ?int $id = null;
    private string $firstName;
    private string $lastName;
    private string $email;
    private int $age;
    private string $password;
    private string $type;
    private ?string $createdAt = null;

    public function __construct(string $firstName, string $lastName, string $email, int $age, string $password, string $type = self::TYPE_NORMAL)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->email     = $email;
        $this->age       = $age;
        $this->password  = $password;
        $this->type      = $type;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getAge(): int { return $this->age; }
    public function getPassword(): string { return $this->password; }
    public function getType(): string { return $this->type; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function isAdmin(): bool { return $this->type === self::TYPE_ADMIN; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setAge(int $age): void { $this->age = $age; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setType(string $type): void { $this->type = $type; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }
}
?>
