# ShoppingCart - REDAXO Warenkorb AddOn

Ein flexibles, modernes Warenkorb-Framework (ohne Bezahlprozess)

## Voraussetzungen

- REDAXO >= 5.1.0
- PHP >= 5.6.0

## Installation

* Release herunterladen und entpacken.
* Ordner umbenennen in `shoppingcart`.
* In den Addons-Ordner legen: /redaxo/src/addons.

Oder den REDAXO-Installer / ZIP-Upload AddOn nutzen!

## Wie benutzen

- [Warenkorb](#cart)
- [Artikel](#cart-item)
- [Warenkorb Storage Implementation](#cart-store)

### <a id="cart"></a>Warenkorb

#### Einen neuen Warenkorb erstellen

Um eine neue Warenkorb-Instanz zu erstellen, musst du eine ID und eine Storage Implementation übermitteln.
Wie man eigene Storage Implementationen zur Verfügung stellt, erfährst du am Ende der README.

```php
$cart = ShoppingCart::factory($id, $cartSessionStore);
// die beiden Parameter sind optional, per Default wird die SessionID und die SessionStore Implementation genutzt.
// möchtest du mehrere Warenkörbe pro User gleichzeitig zur Verfügung stellen, solltest du immer eine eigene ID übergeben.
// public static function factory($cartId = null, $storageImplementation = 'Cart\Storage\SessionStore')
// Möchtest du den CookieStore nutzen, bitte "Cart\Storage\CookieStore" nutzen. Der CookieStore überlebt auch neue Sessions.
```

Die Storage Implementation muss `Cart\Storage\Store` implementieren.
Die Id wird zum Speichern / Wiederherstellen der Warenkorb-Storage Implementation genutzt.

Aktuell gibt es
* SessionStore
* CookieStore
* MemcachedStore
* MemcacheStore
* RedisStore

#### Einen Artikel zum Warenkorb hinzufügen

Benutzen die `add` Methode um einen Artikel zum Warenkorb hinzuzufügen. Ein gültiges `Cart\CartItem` muss der Methode übergeben werden.

```php
$item = new ShoppingCartItem;
$item->name = 'Macbook Pro';
$item->sku = 'MBP8GB';
$item->price = 1200;
$item->tax = 200;
// du kannst unbegrenzt eigene Keys mit eigenen Werten hinzufügen, z.B
// $item->description = "Meine Beschreibung zu diesem Artikel"; 
// bitte beachte, das Price und Tax nur gültige Zahlen erwarten

// hier die Warenkorb Instanz nutzen um den Artikel hinzuzufügen
$cart->add($item);
```
Wenn der Artikel bereits im Warenkorb existiert, wird die Menge um 1 erhöht.

#### Einen Artikel aus dem Warenkorb entfernen

Um einen Artikel aus dem Warenkorb zu entfernen, musst du die interne, vom AddOn generierte Item-Id nutzen und die `remove` Methode aufrufen.

```php
$cart->remove('e4df90d236966195b49b0f01f5ce360a356bc76b');
// diese ID ist nicht deine Datenbank-ID oder Artikel-Id, sondern die Warenkorb-Item-Id. 
// du kommst an diesen Wert über $item->getId() (z.B. in deiner Warenkorb-Ausgabe, als hidden input-feld oder auf einen "Mülleimer-Icon"
```

#### Artikel im Warenkorb aktualisieren

Um eine Eignschaft eines Artikels im Warenkorb zu ändern, musst du die `update` Methode nutzen. Du musst die Warenkorb-Artikel-Id, den Namen der Eigenschaft (der "key", z.B. price) und den neuen Wert übermitteln. Die Methode wird dir die neue Warenkorb-Artikel-ID als Rückgabe übermitteln (falls es sich durch das Update verändert hat)

```php
$newId = $cart->update('e4df90d236966195b49b0f01f5ce360a356bc76b', 'price', 959.99);
```

Wenn du versuchst einen Artikel im Warenkorb zu verändern, welcher nicht existiert, wird ein `InvalidArgumentException` geworfen.

#### Einen Artikel-Objekt aus dem Warenkorb holen

Hol dir ein Artikel-Objekt aus dem Warenkorb mit der Warenkorb-Artikel-ID und der `get` Methode. Wenn der Artikel nicht existiert, wird `null` zurückgegeben.

```php
$item = $cart->get('e4df90d236966195b49b0f01f5ce360a356bc76b');

if ($item) {
    // ...
}
```

####  Alle Artikel aus dem Warenkorb holen

Hole alle Artikel aus dem Warenkorb mit der `all` Methode. Es wird ein `array` aller Artikel aus dem Warenkorb zurückgegeben

```php
$cartItems = $cart->all();

if (count($cartItems) > 0) {
    foreach ($cartItems as $item) {
        // ...
    }
}
```

#### Prüfen ob ein Artikel im Warenkorb existiert

Prüft ob ein Artikel im Warenkorb existiert. Nutze dazu die `has` Methode. Liefert `true` or `false` zurück.

```php
if ($cart->has('e4df90d236966195b49b0f01f5ce360a356bc76b')) {
    // ...
}
```

#### Den Warenkorb leeren

Leere den Warenkorb mit der `clear` Methode.

```php
$cart->clear();
```
Diese Methode leert auch den gespeicherten State für den ausgewählten Warenkorb.

#### Warenkorb State speichern / wiederherstellen

Du kannst den aktuellen Warenkorb mit der `save` Methode speichern.

```php
$cart->save();
```

Die Methode wird die aktuellen Warenkorb-Artikel und Warenkorb-ID in den Store speichern.

Du kannst den Warenkorb wiederherstellen, indem du die `restore` Methode verwendest.

```php
$cart->restore();
```
Diese Methode wird alle zwischengespeicherten Artikel wieder zum Warenkorb hinzufügen und die Warenkorb-Id setzen. Wenn es ein Problem geben sollte, wird ein `Cart\CartRestoreException geworfen. Dies passiert nur, wenn:

- Die gespeicherten Daten nicht serialisiert werden können
- Die nicht-serialisierten Daten ungülig sind (kein array)
- Die Warenkorb-Id nicht in den unserialisierten Daten vorhanden ist
- Die Warenkorb-Artikel nicht in den unserialisierten Daten vorhanden sind
- Die Warenkorb-ID ungültig ist (kein String)
- Die Warenkorb-Artikel ungültig sind (kein Array)

#### Weitere Warenkorb-Methoden

##### Alle einzigartiken Artikel (totalUniqueItems)

Liefert die gesamte Anzahl der eindeutigen Artikel (ohne Mengen einzelner Artikel) im Warenkorb.

```php
$cart->totalUniqueItems();
```

##### Alle Artikel (totalItems)

Liefert die Anzahl aller Artikel (inkl. Mengen) aus dem Warenkorb

```php
$cart->totalItems();
```

##### Gesamtsumme (total)

Die Gesamtsumme aller Artikel aus dem Warenkorb inkl. Steuern. (Brutto)

```php
$cart->total();
```

Du kannst die Gesamtsumme auch Netto (ohne Steuern) aus dem Warenkorb holen. Dazu nutzt du einfach die `totalExcludingTax` Methode.

```php
$cart->totalExcludingTax();
```

##### Steuern (tax)

Die Gesamtsumme der Steuern für alle Artikel im Warenkorb.

```php
$cart->tax();
```

##### toArray

Liefert den Warenkorb-Inhalt als `array`

```php
$cartData = $cart->toArray();
```

Das Array wird folgendermaßen strukturiert sein:

```php
[
    'id' => 'xxyfwq3235werw23wer...', // Warenkorb-ID
    'items' => [
        // Warenkorb-Artikel als Array
    ]
]
```

##### getId

Liefert die ID des Warenkorbs (Default: session_id(), wenn kein eigener Wert übergeben wurde)

```php
$cart->getId();
```

##### getStore

Zeigt an, welche Storage Implementation genutzt wurde (Default: SessionStore)

```php
$cart->getStore();
```

### <a id="cart-item"></a>Warenkorb Artikel

#### Füge einen Artikel zum Warenkorb hinzu

```php
$item = new ShoppingCartItem;

$item->name = 'Macbook Pro';
$item->sku = 'MBP8GB';
$item->price = 1200;
$item->tax = 200;
// oder jeder eigene Key, z.B:
// $item->description = "Meine Beschreibung";
$item->options = [
    'ram' => '8 GB',
    'ssd' => '256 GB'
];

// hier wird erst hinzugefügt, bitte $cart Objekt nutzen.
$cart->add($item);
```

`Cart\CartItem` implementiert `ArrayAccess` so dass die Eigenschaften des Artikels auch wie ein Array behandelt werden können:

```php
$item = new ShoppingCartItem;

$item['name'] = 'Macbook Pro';
$item['sku'] = 'MBP8GB';
$item['price'] = 1200;
$item['tax'] = 200;
// oder $item['description'] oder $item['wasauchimmer']
$item['options'] = [
    'ram' => '8 GB',
    'ssd' => '256 GB'
];

// hier wird erst hinzugefügt, bitte $cart Objekt nutzen.
$cart->add($item);
```

Die Daten können auch direkt als Array an den Warenkorb-Artikel Konstruktor übergeben werden:

```php
$itemData = [
    'name' => 'Macbook Pro',
    'sku' => 'MBP8GB',
    'price' => 1200,
    'tax' => 200,
    'whatever' => 'Mein Wert',
    'options' => [
        'ram' => '8 GB',
        'ssd' => '256 GB'
    ]
];

$item = new ShoppingCartItem($itemData);

// hier wird erst hinzugefügt, bitte $cart Objekt nutzen.
$cart->add($item);
```

* Wird keine Menge (`quantity`) an den Konstruktor übergeben, wird `quantity` per default auf `1` gesetzt. Die Menge kann also auch gleich beeinflusst werden.
* Wird kein Preis (`price`) übergeben, wird per default `0.00` für den Artikel gesetzt.
* Wird keine Steuer (`tax`) übergeben wird per default `0.00` für den Artikel gesetzt. Bitte beachte, dass du die Steuer pro Artikel selbst berechnen musst. Dadurch bist du für jedes Land, jede Steuerart etc. flexibel. Eine einfache, eigene 19 % Berechnung ist auch schnell umgesetzt. Falls du nicht weisst wie, schreib ein Issue.

#### Warenkorb-Artikel ID

Jeder Artikel hat eine einzigartige ID. Diese ID wird anhand der Artikeleigenschaften automatisch generiert. Du kannst die ID mit der `getId` Methode oder der Eigenschaft `id` aufrufen.
Die interne ID kann nicht selbst gesetzt werden. Falls du eine Relation zu deiner Datenbank benötigst, kannst du eine eigene Eigenschaft mittels `$item->meinIdKey = 'foobar'` setzen.

```php
$id = $item->getId();
```

```php
$id = $item->id;
```

```php
$id = $item['id'];
```

**Wird eine Eigenschaft (also ein Artikel-Key) geändert, verändert sich auch die interne Artikel-ID.**

#### Warenkorb-Artikel Methoden

#### get

Hol dir den Wert einer Artikel Eigeschaft über seinen Keynamen.

```php
$name = $item->get('name');
```

Ist nur eine Abkürzung für: 

```php
$name = $item['name'];
```

```php
$name = $item->name;
```

#### set

Setze einen Wert für einen Artikel:

```php
$item->set('name', 'Macbook Pro');
```

Ist nur eine Abkürzung für: 

```php
$item['name'] = 'Macbook Pro';
```

```php
$item->name = 'Macbook Pro';
```

Wenn du die Menge (`quantity`) setzt, muss der Wert ein `integer` sein, ansonsten wird ein `InvalidArgumentException` geworfen.

```php
$item->quantity = 1; // ok
$item->quantity = '1' // wird einen Fehler werfen
```

Wenn du für einen Artikel den Preis oder die Steuer setzt, muss der Wert numerisch (`numeric`) sein, ansonsten wird ein `InvalidArgumentException` geworfen.

```php
$item->price = 10.00; // ok
$item->price = '10' // ok
$item->price = 'ten' // wird einen Fehler werfen
```

##### getTotalPrice

Liefert den gesamten Preis eines Artikels mit Steuern (Brutto). `((artikel preis + artikel steuer) * menge)` [`((item price + item tax) * quantity)]

```php
$item->getTotalPrice();
```

Du kannst die Gesamtsumme des Artikels auch ohne Steuern ermitteln (Netto). Nutze dazu einfach die Methode `getTotalPriceExcludingTax`. `(item price * quantity)`

```php
$item->getTotalPriceExcludingTax();
```

##### getSinglePrice

Ermittle den Einzelpreis des Artikels im Warenkorb inkl. Steuern `(item price + item tax)`

```php
$item->getSinglePrice();
```

Ohne Steuern nutzt du einfach die `getSinglePriceExcludingTax` Methode.

```php
$item->getSinglePriceExcludingTax();
```

##### getTotalTax

Liefert die Gesammtsumme der Steuern für den gewählten Artikel, abhängig zur Menge `(item tax * quantity)`.

```php
$item->getTotalTax();
```

##### getSingleTax

Liefert die Gesamtsumme der Steuern für den gewählten Artikel unabhängig von der Menge.

```php
$item->getSingleTax();
```

##### toArray

Liefert den Artikel als Array.

```php
$itemArr = $item->toArray();
```

Array wird folgendermaßen strukturiert sein:

```php
[
    'id' => 'e4df90d236966195b49b0f01f5ce360a356bc76b', // einzigartike Warenkorb-Artikel-Id
    'data' => [
        'name' => 'Macbook Pro',
        'sku' => 'MBP8GB',
        'price' => 1200,

        // ... weitere Artikel Eigenschaften
    ]
]
```

### <a id="cart-store"></a>Warenkorb Storage Implementation

Ein Warenkorb Storage muss `Cart\Storage\Store` implementieren.

Das AddOn liefern einige Basis Sicherungs-Implementations: `Cart\Storage\SessionStore`, `Cart\Storage\CookieStore`, `Cart\Storage\MemcachedStore`, `Cart\Storage\MemcacheStore`, `Cart\Storage\RedisStore`.

Wenn die `save` Methode des Warenkorbs aufgerufen wird, übermittelt das AddOn die Warenkorb-ID und die serialisierten Daten an die `put` Methode der Storage Implementation.

Wenn die `restore` Methode des Warenkorbs aufgerufen wird, übermittelt das AddOn die Warenkorb-ID an die `get` Methode der Storage Implementation.

Wenn die `clear` Methode des Warenkorbs aufgerufen wird, übermittelt das AddOn die Warenkorb-ID an die `flush` Methode der Storage Implementation.

Eine Beispiel-Implementation könnte so aussehen (kannst du mit Redis, Memcached, MySQL oder wie auch immer umsetzen. Erstelle einfach eine Klasse dazu und leg diese in deinem project AddOn unter "lib" ab)

```php
use Cart\Store;

class SessionStore implements Store
{
    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        return isset($_SESSION[$cartId]) ? $_SESSION[$cartId] : serialize([]);
    }

    /**
     * {@inheritdoc}
     */
    public function put($cartId, $data)
    {
        $_SESSION[$cartId] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function flush($cartId)
    {
        unset($_SESSION[$cartId]);
    }
}
```

# Beispiel Modulausgabe

```php
<?php
$cart = ShoppingCart::factory();

$itemData = [
    'name' => 'Macbook Pro',
    'sku' => 'MBP8GB',
    'price' => 1200,
    'tax' => 200,
    'options' => [
        'ram' => '8 GB',
        'ssd' => '256 GB'
    ]
];
$item = new ShoppingCartItem($itemData);
$cart->add($item);

dump($item);

$item = new ShoppingCartItem;
$item->name = 'Macbook Pro';
$item->sku = 'MBP8GB';
$item->price = 1200;
$item->tax = 200;
$item->meinkey = 'is cool';
$cart->add($item);

$cart->update($item->getId(), 'price', 959.99);

dump($item);
dump($item->getId());
dump($cart);
dump($cart->getStore());
dump($cart->getId());
dump($cart->toArray());
```

# Credits
- Addon, deutsche Doku und Anpassungen für Redaxo by @Hirbod
- @mike182uk, für das Herz dieses AddOns https://github.com/mike182uk/cart
