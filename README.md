# LDTO

A lightweight DTO library inspired by `spatie/data-transfer-object`.

- Attribute based
- Built-in Enum conversion

## Example

``` php

use LDTO\Attr\Convert;
use LDTO\Attr\JsonString;
use LDTO\Attr\NullIsUndefined;
use LDTO\Attr\RawName;
use LDTO\Converter\DateTimeConverter;

enum AuctionType: string
{
    case GamePoint = 'point';
    case UniqueItem = 'item';
}

enum ResourceType: string
{
    case GamePoint = 'point';
    case Gold = 'gold';
}

class AuctionInfo extends \LDTO\DTO
{
    public function __construct(
        #[NullIsUndefined]
        public ?int $id,
        public AuctionType $type,
        public bool $finished,
        public ?string $target,
        #[RawName('host_id')]
        public int $hostID,
        #[RawName('req_resource')]
        public ResourceType $reqResource,

        #[RawName('open_date')]
        #[Convert(DateTimeConverter::class)]
        public \DateTimeImmutable $openDate,
        #[RawName('close_date')]
        #[Convert(DateTimeConverter::class)]
        public \DateTimeImmutable $closeDate,

        #[JsonString]
        public AuctionInfoDetail $detail,
    ) {
    }
}

class AuctionInfoDetail extends \LDTO\DTO
{
    public function __construct(
        public string $title,
        public string $hostName,
        public int $amount,

        public int $startBidAmount,
        #[NullIsUndefined]
        public ?int $finishBidAmount,
        #[NullIsUndefined]
        public ?int $remainCloseDateExtensionCnt,
        #[NullIsUndefined]
        #[Convert(DateTimeConverter::class)]
        public ?\DateTimeImmutable $availableLatestBidCloseDate,
    ) {
    }
}

class AuctionBidItem extends \LDTO\DTO
{
    public function __construct(
        #[NullIsUndefined]
        public ?int $no,
        #[RawName('auction_id')]
        public int $auctionID,

        #[RawName('bidder_id')]
        public int $bidderID,

        public int $amount,

        #[Convert(DateTimeConverter::class)]
        public \DateTimeImmutable $date,
        #[JsonString]
        public AuctionBidItemData $aux,
    ) {
    }
}

class AuctionBidItemData extends \LDTO\DTO
{
    public function __construct(
        public string $bidderName,
        #[NullIsUndefined]
        public ?string $message,
    ) {
    }
}

function example1(DB $db, int $auctionID): AuctionInfo
{
    $raw = $db->queryFirstRow('SELECT * FROM `auction` WHERE id = %i', $auctionID);
    $auction = AuctionInfo::fromArray($raw);

    $date = new DateTimeImmutable();
    $auction->closeDate = $date;
    $auction->finished = true;

    $db->update('auction', $auction->toArray('id'), 'id = %i', $auction->id);
    return $auction;
}

function example2(DB $db, int $auctionID, User $user, int $amount, ?string $msg): void
{
    $newBid = new AuctionBidItem(
        null,
        $auctionID,
        $user->id,
        $amount,
        new DateTimeImmutable(),
        new AuctionBidItemData(
            $user->name,
            $msg,
        )
    );
    $db->insert('auction_bid', $newBid->toArray());
}

```
