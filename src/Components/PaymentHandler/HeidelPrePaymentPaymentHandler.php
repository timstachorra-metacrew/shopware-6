<?php

declare(strict_types=1);

namespace HeidelPayment6\Components\PaymentHandler;

use HeidelPayment6\Components\ClientFactory\ClientFactoryInterface;
use HeidelPayment6\Components\ConfigReader\ConfigReaderInterface;
use HeidelPayment6\Components\PaymentHandler\Traits\CanCharge;
use HeidelPayment6\Components\PaymentHandler\Traits\HasTransferInfoTrait;
use HeidelPayment6\Components\ResourceHydrator\ResourceHydratorInterface;
use HeidelPayment6\Components\TransactionStateHandler\TransactionStateHandlerInterface;
use HeidelPayment6\DataAbstractionLayer\Repository\TransferInfo\HeidelpayTransferInfoRepositoryInterface;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Prepayment;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HeidelPrePaymentPaymentHandler extends AbstractHeidelpayHandler
{
    use CanCharge;
    use HasTransferInfoTrait;

    public function __construct(
        ResourceHydratorInterface $basketHydrator,
        ResourceHydratorInterface $customerHydrator,
        ResourceHydratorInterface $metadataHydrator,
        EntityRepositoryInterface $transactionRepository,
        ConfigReaderInterface $configService,
        TransactionStateHandlerInterface $transactionStateHandler,
        ClientFactoryInterface $clientFactory,
        HeidelpayTransferInfoRepositoryInterface $transferInfoRepository
    ) {
        $this->transferInfoRepository = $transferInfoRepository;

        parent::__construct(
            $basketHydrator,
            $customerHydrator,
            $metadataHydrator,
            $transactionRepository,
            $configService,
            $transactionStateHandler,
            $clientFactory
        );
    }

    /**
     * {@inheritdoc}
     */
    public function pay(
        AsyncPaymentTransactionStruct $transaction,
        RequestDataBag $dataBag,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse {
        parent::pay($transaction, $dataBag, $salesChannelContext);

        try {
            $this->paymentType = $this->heidelpayClient->createPaymentType(new Prepayment());

            $returnUrl = $this->charge($transaction->getReturnUrl());

            /** @var Charge $charge */
            $charge = $this->payment->getChargeByIndex(0);
            $this->saveTransferInfo($transaction->getOrderTransaction()->getId(), $charge, $salesChannelContext->getContext());

            return new RedirectResponse($returnUrl);
        } catch (HeidelpayApiException $apiException) {
            throw new AsyncPaymentProcessException($transaction->getOrderTransaction()->getId(), $apiException->getClientMessage());
        }
    }
}
