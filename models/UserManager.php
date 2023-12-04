<?php
class UserManager
{
    ////////////////////////
    // SESSION MANAGEMENT //
    ////////////////////////
    /**
     * returns a user record (associative array) from SESSION
     */
    public function getLoggedUser()
    {
        if (isset($_SESSION['user']))
            return $_SESSION['user'];
        return null;
    }
    
    /**
     * logs a user in
     * @param string $email
     * @param string $password
     * @return void
     * @throws \Exception
     * @throws UserException
     */
    public function login(string $email, string $password): void
    {
        try { $user = Dtb::getOneRow('SELECT *  FROM `uzivatele` WHERE `email` = ?;', $email); }
        catch (PDOException $ex) { throw new \Exception($ex->getMessage()); }
        
        if (!$user || !password_verify($password, $user['heslo']))
            throw new UserException('Invalid name or password.');
        $_SESSION['user'] = $user;
    }
    
    /**
     * logs a user out
     */
    public function logout(): void
    {
        unset($_SESSION['user']);
    }
    
    /**
     * returns password hash
     * @param string $password
     * @return string
     */
    public function getHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * saves a user activity into the database
     * in case of error only logs it (does not propagate it)
     * @param string $activity
     * @return void
     */
    public function saveUserActivity(string $activity): void
    {
        $user = $this->getLoggedUser();
        if (!$user)
            return;
        
        try {
            $dt = new DateTime();
            $dt = $dt->format("Y-m-d H:i:s");
        } catch (\Exception $ex) {
            return;
        }

        if (!isset($_SERVER["REMOTE_ADDR"]))
            return;

        try {
            Dtb::query("INSERT INTO `aktivity` (`id_uzivatel`, `cas`, `popis`, `ip_adresa`) VALUES(?, ?, ?, ?);", $user["id_uzivatel"], $dt, $activity, $_SERVER["REMOTE_ADDR"]);
        } catch (PDOException $ex) {
            logException($ex);
        }
    }

    
    ////////////////////
    // DTB MANAGEMENT //
    ////////////////////
    /**
     * returns signatures (names and ids) of all users
     * @return array
     * @throws \Exception
     */
    public function getUsersSignatures(): array
    {
        try {
            return Dtb::getTable("SELECT `jmeno`, `id_uzivatel` FROM `uzivatele` ORDER BY `jmeno` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * updates a user password
     * @param int $userId
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function updateUserPassword(int $userId, string $password): void
    {
        try {
            $password = $this->getHash($password);
            Dtb::update("uzivatele", ["heslo" => $password], "WHERE `id_uzivatel` = ?;", [$userId]);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns a user record from dtb
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getUser(int $id): array
    {
        try {
            return Dtb::getOneRow("SELECT * FROM `uzivatele` WHERE `id_uzivatel` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * deletes the user with given id
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteUser(int $id): void
    {
        try {
            Dtb::query("DELETE FROM `uzivatele` WHERE `id_uzivatel` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * updates a user record in dtb
     * @param int $id - user id
     * @param array $user - form data
     * @return void
     * @throws \Exception
     */
    public function updateUser(int $id, array $user): void
    {
        try
        {
            $instMng = new InstitutionManager();
            $institutions = $instMng->getInstitutions();
            foreach ($institutions as $inst)
            {
                if ($inst["id_instituce"] !== $user["id_instituce"] && $inst["id_kontaktni_uzivatel"] === $id)
                {
                    Dtb::query("UPDATE `instituce` SET `id_kontaktni_uzivatel` = NULL WHERE `id_instituce` = ?;", $inst["id_instituce"]);
                    break;
                }
            }
            Dtb::update("uzivatele", $user, "WHERE `id_uzivatel` = ?", [$id]); 
        }
        catch (PDOException $ex)
        {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * creates and returns a basic user validator
     * some items must be added for both user creation and update
     * @return FormValidator
     */
    private function getBasicUserValidator(): FormValidator
    {
        $validator = new FormValidator();
        
        $item = new FormValidatorItem("id_instituce", "Institution");
        $item->addRuleIsNotEmpty()
                ->addRuleIsKeyFromTable("instituce", "id_instituce");
        $validator->addItem($item);

        $item = new FormValidatorItem("jmeno", "Name");
        $item->addRuleIsNotEmpty();
        $validator->addItem($item);

        $item = new FormValidatorItem("admin", "Admin");
        $item->addRuleIsNotEmpty()
                ->addRuleIsInt();
        $validator->addItem($item);
        
        return $validator;
    }
    
    /**
     * creates and returns a validator for user creation
     * @return FormValidator
     */
    public function getNewUserValidator(): FormValidator
    {
        $validator = $this->getBasicUserValidator();
        
        $item = new FormValidatorItem("email", "E-mail");
        $item->addRuleIsNotEmpty()
                ->addRuleIsUnique("uzivatele", "email");
        $validator->addItem($item);

        $item = new FormValidatorItem("heslo", "Password");
        $item->addRuleIsNotEmpty();
        $validator->addItem($item);
        
        return $validator;
    }
    
    /**
     * creates and returns a validator for user update
     * @param int $id
     * @return FormValidator
     */
    public function getEditUserValidator(int $id): FormValidator
    {
        $validator = $this->getBasicUserValidator();
        
        $item = new FormValidatorItem("email", "E-mail");
        $item->addRuleIsNotEmpty()
                ->addRuleIsUniqueExcept("uzivatele", "email", "id_uzivatel", $id);
        $validator->addItem($item);
        $validator->addItem(new FormValidatorItem("heslo", "Password"));
        
        return $validator;
    }
    
}



































