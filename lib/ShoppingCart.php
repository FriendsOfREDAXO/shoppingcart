<?php

/**
* shoppingcart Addon.
*
* @author Friends Of REDAXO
*
* @var rex_addon
*/
use Cart\Cart;

class ShoppingCart extends Cart
{
    public static function factory($cartId = null, $storageImplementation = 'Cart\Storage\SessionStore')
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!$cartId) {
            $cartId = session_id();
        }
        $cartSessionStore = new $storageImplementation();

        return new Cart($cartId, $cartSessionStore);
    }
}
