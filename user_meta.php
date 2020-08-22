<?php
// your custom user_meta code here
function account_migrate_create_user_metta($user_id, $user){
    add_user_meta( $user_id,  'dealer_id', $user['id'] ,  true );
    add_user_meta( $user_id,  'dealer_username', $user['username'] ,  true );
    add_user_meta( $user_id,  'dealer_acct', $user['acct'] ,  true );
    add_user_meta( $user_id,  'dealer_name', $user['name'] ,  true );
    add_user_meta( $user_id,  'dealer_country', $user['country'] ,  true );
    add_user_meta( $user_id,  'dealer_admin', $user['admin'] ,  true );
    add_user_meta( $user_id,  'dealer_pricing', $user['pricing'] ,  true );
}