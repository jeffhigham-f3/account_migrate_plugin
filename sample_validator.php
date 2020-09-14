namespace AccountMigrate;

class Password {

    public static function validate($password, $dbPassword){

        // begin your code
        return ($password == $dbPassword);
        // end your code

    }

}