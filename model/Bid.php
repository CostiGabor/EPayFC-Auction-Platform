<?php
require_once "framework/Model.php";
class Bid extends Model
{
    public function __construct(
        private int $owner,
        private int $item,
        private string $created_at,
        private float $amount
    ) {}

    public static function getBidsByItem(int $id): array{
        $query = self::execute(
            "SELECT * FROM bids WHERE item = :id ORDER BY amount DESC",
            ['id' => $id]
        );

        $datas = $query->fetchAll();
        $bids = [];

        foreach ($datas as $bid) {
            $bids[] = new Bid(
                $bid['owner'],
                $bid['item'],
                $bid['created_at'],
                (float) $bid['amount']
            );
        }
        return $bids;
    }

    public static function getBiggerBidByItem(int $itemId): ?Bid
    {
        $query = self::execute(
            "SELECT * FROM bids 
            WHERE item = :id 
            ORDER BY amount DESC 
            LIMIT 1",
            ['id' => $itemId]
        );

        $bid = $query->fetch();

        if (!$bid) {
            return null; // aucune enchère
        }

        return new Bid(
            $bid['owner'],
            $bid['item'],
            $bid['created_at'],
            (float) $bid['amount']
        );
    }

    public function save(): Bid{
        self::execute(
            "INSERT INTO Bids(owner,item,created_at,amount)
            VALUES(?,?,?,?)",
            [$this->owner,$this->item,$this->created_at,$this->amount]
        );
        return $this;
    }
 
    public function delete(): void{
        self::execute(
            "DELETE FROM bids 
            WHERE owner = :owner 
            AND item = :item 
            AND created_at = :created_at",
            [
                "owner" => $this->owner,
                "item" => $this->item,
                "created_at" => $this->created_at
            ]
        );        
    }


    public function getOwner(): int {
        return $this->owner;
    }

    public function getOwnerPseudo(): string{
        return User::get_member_by_id($this->owner)->getPseudo();
    }

    public function getItem(): int {
        return $this->item;
    }

    public function getItemObject() : Item {
        $query = self::execute(
            "SELECT * from items
            where id= :item",['item'=>$this->item]
        );
        $data = $query->fetch();

        return new Item(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['owner'],
            $data['created_at'],
            $data['buy_now_price'],
            $data['duration_days'],
            $data['starting_bid']
        );
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    // Partie validation
    public static function validate_newMax_bid(int $itemIdBid, float $amount): array {
        $errors = [];

        $amount = (float)$amount;
        if ($amount <= 0) {
            $errors[] = "Le montant doit être supérieur à 0.";
            return $errors;
        }
        // 1) On vérifie si il existe un bid.
        $queryExistingBid = self::execute(
            "SELECT * FROM Bids WHERE item= :itemId",
            [
                "itemId" => $itemIdBid,
            ]
        );

        if($queryExistingBid->rowCount() > 0) {
        // 2) Si il existe un bid on vérifie que le montant proposé est supérieur au plus haut bid.
           return Bid::validate_existing_bid($itemIdBid,$amount);
        }else{
            // 1.2 Si pas de bid alors, le montant proposé doit être supérieur au bid minimum attendu.
            return Bid::validate_starting_bid_respected($itemIdBid,$amount);
        }

        return $errors;
    }

    public static function validate_existing_bid(int $itemIdBid, float $amount): array{
        $errors = [];
        $query = self::execute(
            "SELECT * FROM Bids WHERE item = :itemId AND amount >= :amount",
            [
                "itemId" => $itemIdBid,
                "amount" => $amount,
            ]
        );
        if($query->rowCount() > 0) {
            $errors[]= "Le montant proposé doit etre supérieure au current bid.";
            return $errors;
        };
        return $errors;
    }

    public static function validate_starting_bid_respected(int $itemIdBid, float $amount): array {
        $errors = [];
        $query = self::execute(
            "SELECT * FROM Items WHERE id = :itemId AND starting_bid >= :amount",
            [
            "itemId" => $itemIdBid,
            "amount" => $amount,
            ]
        );
        if($query->rowCount() > 0) {
            $errors[]= "Le montant proposé doit etre supérieure au montant minimum demandé.";
            return $errors;
        };
        return $errors;
    }


}