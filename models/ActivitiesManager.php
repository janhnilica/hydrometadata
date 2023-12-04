<?php
class ActivitiesManager
{
    /**
     * returns all activities of given user
     * @param int $id - user id
     * @return array
     * @throws \Exception
     */
    public function getUserActivities(int $id): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `aktivity` WHERE `id_uzivatel` = ? ORDER BY `cas` DESC;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * deletes all activities of given user
     * @param int $id - user id
     * @return void
     * @throws \Exception
     */
    public function deleteUserActivities(int $id): void
    {
        try {
            Dtb::query("DELETE FROM `aktivity` WHERE `id_uzivatel` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
