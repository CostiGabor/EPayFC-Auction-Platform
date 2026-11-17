<?php
require_once "framework/Model.php";
class VueItemStatus extends Model
{
    private Item $item;

    public function __construct(
        Item $item,
        private ?string $end_at,
        private int $bid_count,
        private ?float $max_bid,
        private bool $is_direct_sale,
        private bool $is_auction,
        private bool $has_buy_now,
        private bool $has_bids,
        private bool $buy_now_reached,
        private bool $not_purchased_direct_sale
    ) {
        $this->item = $item;
    }

    
    public function getItem(): Item {
        return $this->item;
    }

    public static function get_item_status(int $id): ?VueItemStatus{
        $query = self::execute(
            "SELECT * FROM v_items_status WHERE id = :id",
            ['id' => $id]
        );

        $data = $query->fetch();
        
        if (!$data) return null;

        $item = new Item(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['owner'],
            $data['created_at'],
            $data['buy_now_price'],
            $data['duration_days'],
            $data['starting_bid']
        );

        return new VueItemStatus(
            $item,
            $data['end_at'],
            $data['bid_count'],
            $data['max_bid'],
            (bool) $data['is_direct_sale'],
            (bool) $data['is_auction'],
            (bool) $data['has_buy_now'],
            (bool) $data['has_bids'],
            (bool) $data['buy_now_reached'],
            (bool) $data['not_purchased_direct_sale']
        );
    }
    public function getOwner(): User|null{
        $query = self::execute(
            "SELECT * FROM users WHERE id = :id",
            ["id" => $this->getItem()->getOwner()]
        );

        $data = $query->fetch();

        if (!$data) {
            return null;
        }

        return new User(
            (int) $data["id"],
            $data["full_name"],
            $data["email"],
            $data["pseudo"],
            $data["password"],
            $data["role"] ?? "user",
            $data["picture_path"] ?? null,
            $data["iban"] ?? null
        );

    }
    public function getEndAt(): ?string { 
        return $this->end_at; 
    }

    public function getBidCount(): int {
        return $this->bid_count;
    }

    public function getMaxBid(): ?float {
        return $this->max_bid; 
    }

    public function isDirectSale(): bool { 
        return $this->is_direct_sale; 
    }

    public function isAuction(): bool { 
        return $this->is_auction; 
    }

    public function hasBuyNow(): bool { 
        return $this->has_buy_now; 
    }

    public function hasBids(): bool {
        return $this->has_bids; 
    }
    public function isBuyNowReached(): bool {
        return $this->buy_now_reached;
    }
    public function isNotPurchasedDirectSale(): bool {
        return $this->not_purchased_direct_sale; 
    }

    public function isEnded(): bool
    {
        $currentTime = AppTime::get_current_datetime();
        return $this->end_at <= $currentTime;
    }

    public function isClosed(): bool{
        return $this->isEnded() || $this->isBuyNowReached();
    }

    public function validation_isBuyNowReached(): array{
         $errors = [];

        if($this->isBuyNowReached()==1) {
                $errors[]= "Désolé, l'item n'est plus disponible !";
                return $errors;
            };
        return $errors;
    }
}