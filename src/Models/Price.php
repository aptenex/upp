<?php

namespace Aptenex\Upp\Models;

use Money\Money;
use Aptenex\Upp\Exception\Error;
use Aptenex\Upp\Util\ArrayUtils;
use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Calculation\Stay;
use Aptenex\Upp\Exception\ErrorHandler;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Calculation\AdjustmentAmount;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Aptenex\Upp\Calculation\SplitAmount\GuestSplitOverview;

class Price
{

    /**
     * @var Money
     */
    protected $total;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var Money
     */
    protected $basePrice;

    /**
     * @var Money
     */
    protected $damageDeposit;

    /**
     * @var string
     */
    protected $damageDepositSplitMethod;

    /**
     * @var GuestSplitOverview
     */
    protected $splitDetails;

    /**
     * @var AdjustmentAmount[]
     */
    protected $adjustments = [];

    /**
     * @var ErrorHandler
     */
    protected $errors;

    /**
     * @var Stay
     */
    protected $stay;

    /**
     * @var PricingContext
     */
    protected $contextUsed;

    /**
     * @var string
     */
    protected $bookableType = Period::BOOKABLE_TYPE_ENQUIRY_ONLY;

    /**
     * FinalPrice constructor.
     *
     * @param PricingContext $contextUsed
     */
    public function __construct(PricingContext $contextUsed)
    {
        if (!empty($contextUsed->getCurrency())) {
            $this->currency = strtoupper(trim($contextUsed->getCurrency()));
            $this->total = MoneyUtils::newMoney(0, $this->getCurrency());
            $this->basePrice = MoneyUtils::newMoney(0, $this->getCurrency());
            $this->damageDeposit = MoneyUtils::newMoney(0, $this->getCurrency());
        }

        $this->stay = new Stay($contextUsed);
        $this->splitDetails = new GuestSplitOverview();
        $this->errors = new ErrorHandler();
        $this->contextUsed = $contextUsed;
    }
    
    /**
     * Take extreme caution in using this method.
     * Setting the context after constructing will not change any calculations.
     * The only reason you would want to use this is if you know that you have manually ammended
     * values on the price, and need to update the context to reflect the changes applied.
     * For example, if you convert currencies, you may need the context to reflect the currencies.
     * @param PricingContext $contextUsed
     */
    public function setContextUsed(PricingContext $contextUsed): void
    {
        $this->contextUsed = $contextUsed;
    }
    
    

    /**
     * This should never be needed with the exception of converting one rate response to another.
     * In this case, we will need to change the currency.
     * @param string $currency
     */
    public function setCurrency(string $currency)
    {
        $this->currency = strtoupper(trim($currency));
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return Money
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param Money $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return Money
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param Money $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return Money
     */
    public function getDamageDeposit()
    {
        return $this->damageDeposit;
    }

    /**
     * @param Money $damageDeposit
     */
    public function setDamageDeposit($damageDeposit)
    {
        $this->damageDeposit = $damageDeposit;
    }

    /**
     * @return AdjustmentAmount[]
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }

    /**
     * @param AdjustmentAmount[] $adjustments
     */
    public function setAdjustments($adjustments)
    {
        $this->adjustments = $adjustments;
    }

    /**
     * @param $adjustmentType
     *
     * @return bool
     */
    public function hasAdjustmentByType($adjustmentType)
    {
        foreach ($this->adjustments as $adj) {
            if ($adj->getType() === $adjustmentType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AdjustmentAmount $adjustment
     */
    public function addAdjustment(AdjustmentAmount $adjustment)
    {
        $this->adjustments[] = $adjustment;
    }

    /**
     * @return PricingContext
     */
    public function getContextUsed()
    {
        return $this->contextUsed;
    }


    /**
     * @return Stay
     */
    public function getStay()
    {
        return $this->stay;
    }

    /**
     * @return string
     */
    public function getBookableType()
    {
        return $this->bookableType;
    }

    /**
     * @param string $bookableType
     */
    public function setBookableType($bookableType)
    {
        $this->bookableType = $bookableType;
    }

    /**
     * @return GuestSplitOverview
     */
    public function getSplitDetails()
    {
        return $this->splitDetails;
    }

    /**
     * @param GuestSplitOverview $splitDetails
     */
    public function setSplitDetails(GuestSplitOverview $splitDetails)
    {
        $this->splitDetails = $splitDetails;
    }

    public function disableSplitDetails()
    {
        $this->splitDetails = null;
    }

    /**
     * @return array
     */
    protected function getAdjustmentsArray()
    {
        $d = [];

        foreach ($this->getAdjustments() as $a) {
            $d[] = $a->__toArray();
        }

        return $d;
    }

    public function addErrorsFromViolations(ConstraintViolationList $violations)
    {
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $type = Error::TYPE_OTHER;
            if (defined("Aptenex\Upp\Exception\Error::TYPE_" . $violation->getCode())) {
                $type = constant("Aptenex\Upp\Exception\Error::TYPE_" . $violation->getCode());
            }
            $unit = null;
            if($violation->getParameters()){
                $params = $violation->getParameters();
                if(isset($params['unit'])){
                    $unit = $params['unit'];
                }
            }
            
            $error = new Error($type, $unit, "Request Error on '" . $violation->getPropertyPath() . "' : " . $violation->getMessage());
            

            
            $this->addError($error);
        }

    }

    /**
     * @return ErrorHandler
     */
    public function getErrors(): ErrorHandler
    {
        return $this->errors;
    }

    /**
     * @param $error
     */
    public function addError(Error $error)
    {
        $this->errors->addError($error);
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->errors->hasErrors();
    }

    /**
     * @return string
     */
    public function getDamageDepositSplitMethod()
    {
        return $this->damageDepositSplitMethod;
    }

    /**
     * @param string $damageDepositSplitMethod
     */
    public function setDamageDepositSplitMethod($damageDepositSplitMethod)
    {
        $this->damageDepositSplitMethod = $damageDepositSplitMethod;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'currency'                 => $this->getCurrency(),
            'description'              => !empty($this->getContextUsed()->getDescription()) ? $this->getContextUsed()->getDescription() : null,
            'total'                    => MoneyUtils::getConvertedAmount($this->getTotal()),
            'basePrice'                => MoneyUtils::getConvertedAmount($this->getBasePrice()),
            'damageDeposit'            => MoneyUtils::getConvertedAmount($this->getDamageDeposit()),
            'damageDepositSplitMethod' => $this->getDamageDepositSplitMethod(),
            'bookableType'             => $this->getBookableType(),
            'adjustments'              => $this->getAdjustmentsArray(),
            'stayBreakdown'            => $this->getStay()->__toArray(),
            'splitDetails'             => !is_null($this->splitDetails) ? $this->splitDetails->__toArray() : null,
            'errors'                   => $this->getErrors()->__toArray(),
        ];
    }

    /**
     * This will not parse the stay breakdown field as it is usually not required.
     *
     * @param array $data
     *
     * @return Price
     */
    public function fromArray($data)
    {
        $this->setTotal(MoneyUtils::fromString($data['total'], $data['currency']));
        $this->setBasePrice(MoneyUtils::fromString($data['basePrice'], $data['currency']));
        $this->setBookableType($data['bookableType']);

        $adjustments = [];

        foreach ($data['adjustments'] as $a) {
            $money = MoneyUtils::fromString($a['amount'], $data['currency']);
            $adjustments[] = new AdjustmentAmount(
                $money,
                $a['identifier'],
                $a['description'],
                $a['calculationOperand'],
                $a['type'],
                $a['priceGroup'],
                $a['guestSplitMethod'],
                $a['hidden'] ?? true
            );
        }

        $this->setAdjustments($adjustments);
        $this->setDamageDeposit(MoneyUtils::fromString($data['damageDeposit'], $data['currency']));
        $this->setDamageDepositSplitMethod($data['damageDepositSplitMethod'] ?? null);

        $this->stay = new Stay($this->getContextUsed());

        $this->splitDetails = new GuestSplitOverview();
        if (ArrayUtils::hasNestedArrayValue('splitDetails.deposit', $data)) {
            $sdData = $data['splitDetails'];

            $sdObject = $this->getSplitDetails();

            $sdObject->setDeposit(MoneyUtils::fromString($sdData['deposit']['amount'], $data['currency']));

            $sdObject->setDepositCalculationType(ArrayUtils::getNestedArrayValue(
                'deposit.calculationType',
                $sdData,
                GuestSplitOverview::DEPOSIT_CALCULATION_TYPE_DEFAULT
            ));

            $sdObject->setDepositDueDate(new \DateTime($sdData['deposit']['dueDate']));

            $sdObject->setBalance(MoneyUtils::fromString($sdData['balance']['amount'], $data['currency']));
            $sdObject->setBalanceDueDate(new \DateTime($sdData['balance']['dueDate']));

            $sdObject->setDamageDepositSplitMethod($sdData['damageDepositSplitMethod']);
        } else {
            $this->disableSplitDetails(); // Does not exist, disable
        }

        if ($data['currency']) {
            $this->currency = $data['currency'];
        }

        if (isset($data['errors']) && is_array($data['errors'])) {
            foreach ($data['errors'] as $errorData) {
                if (!isset($errorData['type'])) {
                    continue; // Type is ALWAYS required
                }

                $this->errors->addError(Error::fromArrayData($errorData));
            }
        }

        return $this;
    }

}