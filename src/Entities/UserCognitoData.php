<?php declare(strict_types=1);
namespace Budgetcontrol\Authentication\Entities;

final class UserCognitoData
{
    private string $username;
    private string $email;
    private string $name;
    private string $sub;
    private array $attribute;

    public static function create(string $username, string $email, string $sub): self
    {
        return new self($username, $email, $sub);
    }

    private function __construct(string $username, string $email, string $sub)
    {
        $this->username = $username;
        $this->email = $email;
        $this->sub = $sub;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    /**
     * Get the value of attribute
     *
     * @return array
     */
    public function getAttribute(): array
    {
        return $this->attribute;
    }

    /**
     * Set the value of attribute
     *
     * @param array $attribute
     *
     * @return self
     */
    public function setAttribute(string $attribute, mixed $value): self
    {
        $this->attribute[$attribute] = $value;
        return $this;
    }



    /**
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}