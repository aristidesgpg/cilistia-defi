<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CommerceAccountController;
use App\Http\Controllers\CommerceCustomerController;
use App\Http\Controllers\CommercePaymentController;
use App\Http\Controllers\CommerceTransactionController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ExchangeTradeController;
use App\Http\Controllers\FeatureLimitController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\GiftcardBrandController;
use App\Http\Controllers\GiftcardContentController;
use App\Http\Controllers\GiftcardController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\GridController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Page;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PeerOfferController;
use App\Http\Controllers\PeerPaymentMethodController;
use App\Http\Controllers\PeerTradeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StakeController;
use App\Http\Controllers\StakePlanController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TransferRecordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserVerificationController;
use App\Http\Controllers\WalletAccountController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebHook\CoinController;
use Illuminate\Support\Facades\Route;
use Spatie\Csp\AddCspHeaders;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('web')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [LoginController::class, 'login'])->name('login');
        Route::post('register', [RegisterController::class, 'register'])->name('register')->block();
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::prefix('reset-password')->name('reset-password.')->group(function () {
            Route::post('reset', [ResetPasswordController::class, 'reset'])->name('reset')->block();
            Route::post('send-email-code', [ResetPasswordController::class, 'sendEmailCode'])->name('send-email-code')->block();
            Route::post('request-token', [ResetPasswordController::class, 'requestToken'])->name('request-token')->block();
        });
    });

    Route::middleware('auth')->group(function () {
        Route::prefix('wallets')->name('wallet.')->group(function () {
            Route::get('unused', [WalletController::class, 'unused'])->name('unused');

            Route::prefix('{wallet}')->group(function () {
                Route::post('create-account', [WalletController::class, 'createAccount'])->name('create-account')->block();
                Route::get('price', [WalletController::class, 'price'])->name('price');
                Route::post('market-chart', [WalletController::class, 'marketChart'])->name('market-chart');
            });
        });

        Route::prefix('transfer-records')->name('transfer-record.')->group(function () {
            Route::get('', [TransferRecordController::class, 'paginate'])->name('paginate');
        });

        Route::prefix('wallet-accounts')->name('wallet-account.')->group(function () {
            Route::get('all', [WalletAccountController::class, 'all'])->name('all');
            Route::get('total-available-price', [WalletAccountController::class, 'totalAvailablePrice'])->name('total-available-price');
            Route::get('aggregate-price', [WalletAccountController::class, 'aggregatePrice'])->name('aggregate-price');

            Route::prefix('{id}')->group(function () {
                Route::post('send', [WalletAccountController::class, 'send'])->name('send')->block();
                Route::get('latest-address', [WalletAccountController::class, 'latestAddress'])->name('latest-address');
                Route::post('generate-address', [WalletAccountController::class, 'generateAddress'])->name('generate-address')->block();
                Route::post('estimate-fee', [WalletAccountController::class, 'estimateFee'])->name('estimate-fee');
            });
        });

        Route::prefix('banks')->name('bank.')->group(function () {
            Route::get('all', [BankController::class, 'all'])->name('all');
        });

        Route::prefix('bank-accounts')->name('bank-account.')->group(function () {
            Route::post('', [BankAccountController::class, 'create'])->name('create')->block();
            Route::get('all', [BankAccountController::class, 'all'])->name('all');

            Route::prefix('{id}')->group(function () {
                Route::delete('', [BankAccountController::class, 'delete'])->name('delete')->block();
            });
        });

        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('account', [PaymentController::class, 'getAccount'])->name('account');
            Route::get('deposit-methods', [PaymentController::class, 'getDepositMethods'])->name('deposit-methods');
            Route::get('daily-chart', [PaymentController::class, 'getDailyChart'])->name('daily-chart');
            Route::post('deposit', [PaymentController::class, 'deposit'])->name('deposit')->block();
            Route::get('transactions', [PaymentController::class, 'transactionPaginate'])->name('transaction-paginate');
            Route::post('withdraw', [PaymentController::class, 'withdraw'])->name('withdraw')->block();
        });

        Route::prefix('user')->name('user.')->group(function () {
            Route::get('', [UserController::class, 'get'])->name('get');
            Route::patch('', [UserController::class, 'update'])->name('update')->block();
            Route::get('notification-settings', [UserController::class, 'getNotificationSettings'])->name('notification-settings');
            Route::patch('notification-settings', [UserController::class, 'updateNotificationSettings'])->name('update-notification-settings');
            Route::post('upload-picture', [UserController::class, 'uploadPicture'])->name('upload-picture')->block();
            Route::post('change-password', [UserController::class, 'changePassword'])->name('change-password')->block();
            Route::post('get-two-factor', [UserController::class, 'getTwoFactor'])->name('get-two-factor');
            Route::post('reset-two-factor', [UserController::class, 'resetTwoFactor'])->name('reset-two-factor')->block();
            Route::post('set-two-factor', [UserController::class, 'setTwoFactor'])->name('set-two-factor')->block();
            Route::post('verify-phone-with-token', [UserController::class, 'verifyPhoneWithToken'])->name('verify-phone-with-token');
            Route::post('verify-email-with-token', [UserController::class, 'verifyEmailWithToken'])->name('verify-email-with-token');
            Route::post('set-online', [UserController::class, 'setOnline'])->name('set-online');
            Route::post('set-away', [UserController::class, 'setAway'])->name('set-away');
            Route::post('set-offline', [UserController::class, 'setOffline'])->name('set-offline');
            Route::get('activities', [UserController::class, 'activityPaginate'])->name('activity-paginate');

            Route::prefix('verification')->name('verification.')->group(function () {
                Route::get('', [UserVerificationController::class, 'get'])->name('get');
                Route::get('documents', [UserVerificationController::class, 'getDocuments'])->name('get-documents');
                Route::get('address', [UserVerificationController::class, 'getAddress'])->name('get-address');
                Route::post('documents', [UserVerificationController::class, 'uploadDocument'])->name('upload-document')->block();
                Route::post('address', [UserVerificationController::class, 'updateAddress'])->name('update-address')->block();
            });

            Route::prefix('notifications')->name('notification.')->group(function () {
                Route::get('', [UserNotificationController::class, 'paginate'])->name('paginate');
                Route::get('total-unread', [UserNotificationController::class, 'totalUnread'])->name('total-unread');
                Route::post('mark-all-as-read', [UserNotificationController::class, 'markAllAsRead'])->name('mark-all-as-read')->block();
                Route::post('clear', [UserNotificationController::class, 'clear'])->name('clear')->block();

                Route::prefix('{id}')->group(function () {
                    Route::patch('mark-as-read', [UserNotificationController::class, 'markAsRead'])->name('mark-as-read')->block();
                });
            });
        });

        Route::prefix('user-profiles/{user:name}')->name('user-profile.')->group(function () {
            Route::get('', [UserProfileController::class, 'get'])->name('get');
            Route::post('unfollow', [UserProfileController::class, 'unfollow'])->name('unfollow')->block();
            Route::post('follow', [UserProfileController::class, 'follow'])->name('follow')->block();
            Route::get('followers', [UserProfileController::class, 'followersPaginate'])->name('followers-paginate');
            Route::get('following', [UserProfileController::class, 'followingPaginate'])->name('following-paginate');
            Route::get('reviews', [UserProfileController::class, 'reviewsPaginate'])->name('reviews-paginate');

            Route::prefix('peer-offers')->name('peer-offer.')->group(function () {
                Route::get('', [UserProfileController::class, 'peerOfferPaginate'])->name('paginate');
            });
        });

        Route::prefix('exchange-trades')->name('exchange-trade.')->group(function () {
            Route::post('calculate-buy', [ExchangeTradeController::class, 'calculateBuy'])->name('calculate-buy');
            Route::post('buy', [ExchangeTradeController::class, 'buy'])->name('buy')->block();
            Route::post('calculate-sell', [ExchangeTradeController::class, 'calculateSell'])->name('calculate-sell');
            Route::post('sell', [ExchangeTradeController::class, 'sell'])->name('sell')->block();
            Route::get('', [ExchangeTradeController::class, 'paginate'])->name('paginate');
        });

        Route::prefix('giftcard-brands')->name('giftcard-brand.')->group(function () {
            Route::get('all', [GiftcardBrandController::class, 'all'])->name('all');
        });

        Route::prefix('giftcards')->name('giftcard.')->group(function () {
            Route::get('cart', [GiftcardController::class, 'cart'])->name('cart');
            Route::post('purchase', [GiftcardController::class, 'purchase'])->name('purchase')->block();
            Route::get('', [GiftcardController::class, 'paginate'])->name('paginate');
        });

        Route::prefix('giftcard-contents')->name('giftcard-content.')->group(function () {
            Route::get('', [GiftcardContentController::class, 'paginate'])->name('paginate');
        });

        Route::prefix('feature-limits')->name('feature-limit.')->group(function () {
            Route::get('all', [FeatureLimitController::class, 'all'])->name('all');
        });

        Route::name('email-verification.')->group(function () {
            Route::middleware(['signed'])->get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verify');
            Route::middleware(['throttle:6,60'])->post('email/verification-notification', [EmailVerificationController::class, 'sendEmail'])->name('send');
        });

        Route::prefix('token')->name('token.')->group(function () {
            Route::post('send-phone', [TokenController::class, 'sendPhone'])->name('send-phone')->block();
            Route::post('send-email', [TokenController::class, 'sendEmail'])->name('send-email')->block();
        });

        Route::prefix('grids')->name('grid.')->group(function () {
            Route::post('all', [GridController::class, 'all'])->name('all');

            Route::middleware('permission:access_control_panel')->group(function () {
                Route::post('reset-dimensions', [GridController::class, 'resetDimensions'])->name('reset-dimensions');
                Route::post('set-dimensions', [GridController::class, 'setDimensions'])->name('set-dimensions');
            });
        });

        Route::prefix('peer-offers')->name('peer-offer.')->group(function () {
            Route::post('', [PeerOfferController::class, 'create'])->name('create')->block();
            Route::get('', [PeerOfferController::class, 'paginate'])->name('paginate');

            Route::prefix('{offer}')->group(function () {
                Route::get('', [PeerOfferController::class, 'get'])->name('get');
                Route::post('start-trade', [PeerOfferController::class, 'startTrade'])->name('start-trade')->block();
                Route::get('bank-accounts', [PeerOfferController::class, 'getBankAccounts'])->name('get-bank-accounts');
                Route::patch('enable', [PeerOfferController::class, 'enable'])->name('enable')->block();
                Route::patch('disable', [PeerOfferController::class, 'disable'])->name('disable')->block();
                Route::patch('close', [PeerOfferController::class, 'close'])->name('close')->block();
            });
        });

        Route::prefix('peer-trades')->name('peer-trade.')->group(function () {
            Route::get('sell', [PeerTradeController::class, 'sellPaginate'])->name('sell-paginate');
            Route::get('sell-statistics', [PeerTradeController::class, 'getSellStatistics'])->name('get-sell-statistics');
            Route::get('buy', [PeerTradeController::class, 'buyPaginate'])->name('buy-paginate');
            Route::get('buy-statistics', [PeerTradeController::class, 'getBuyStatistics'])->name('get-buy-statistics');

            Route::prefix('{trade}')->middleware('can:view,trade')->group(function () {
                Route::get('', [PeerTradeController::class, 'get'])->name('get');
                Route::get('participants', [PeerTradeController::class, 'getParticipants'])->name('get-participants');
                Route::post('upload-file', [PeerTradeController::class, 'uploadFile'])->name('upload-file')->block();
                Route::get('download-file/{id}', [PeerTradeController::class, 'downloadFile'])->name('download-file');
                Route::post('mark-read', [PeerTradeController::class, 'markRead'])->name('mark-read')->block();
                Route::post('send-message', [PeerTradeController::class, 'sendMessage'])->name('send-message')->block();
                Route::get('messages', [PeerTradeController::class, 'messagePaginate'])->name('message-paginate');
                Route::patch('complete', [PeerTradeController::class, 'complete'])->name('complete')->middleware('two-factor')->block();
                Route::patch('cancel', [PeerTradeController::class, 'cancel'])->name('cancel')->block();
                Route::patch('confirm', [PeerTradeController::class, 'confirm'])->name('confirm')->block();
                Route::patch('dispute', [PeerTradeController::class, 'dispute'])->name('dispute')->block();
                Route::post('rate-seller', [PeerTradeController::class, 'rateSeller'])->name('rate-seller')->block();
                Route::post('rate-buyer', [PeerTradeController::class, 'rateBuyer'])->name('rate-buyer')->block();
            });
        });

        Route::prefix('peer-payment-methods')->name('peer-payment-method.')->group(function () {
            Route::get('all', [PeerPaymentMethodController::class, 'all'])->name('all');
        });

        Route::prefix('stakes')->name('stake.')->group(function () {
            Route::get('', [StakeController::class, 'paginate'])->name('paginate');
            Route::get('statistics', [StakeController::class, 'getStatistics'])->name('get-statistics');
        });

        Route::prefix('stake-plans')->name('stake-plan.')->group(function () {
            Route::get('', [StakePlanController::class, 'paginate'])->name('paginate');

            Route::prefix('{plan}')->group(function () {
                Route::post('stake', [StakePlanController::class, 'stake'])->name('stake')->block();
            });
        });

        Route::prefix('commerce-account')->name('commerce-account.')->group(function () {
            Route::get('', [CommerceAccountController::class, 'get'])->name('get');
            Route::patch('', [CommerceAccountController::class, 'update'])->name('update');
            Route::post('set-logo', [CommerceAccountController::class, 'setLogo'])->name('set-logo');
            Route::post('', [CommerceAccountController::class, 'create'])->name('create');
        });

        Route::prefix('commerce-transactions')->name('commerce-transaction.')->group(function () {
            Route::get('', [CommerceTransactionController::class, 'paginate'])->name('paginate');
            Route::get('status-statistics', [CommerceTransactionController::class, 'getStatusStatistics'])->name('get-status-statistics');
            Route::get('statistics', [CommerceTransactionController::class, 'getStatistics'])->name('get-statistics');
            Route::get('wallet-aggregate', [CommerceTransactionController::class, 'getWalletAggregate'])->name('get-wallet-aggregate');
            Route::get('chart', [CommerceTransactionController::class, 'getChart'])->name('get-chart');
        });

        Route::prefix('commerce-customers')->name('commerce-customer.')->group(function () {
            Route::post('', [CommerceCustomerController::class, 'create'])->name('create')->block();
            Route::get('', [CommerceCustomerController::class, 'paginate'])->name('paginate');
            Route::get('statistics', [CommerceCustomerController::class, 'getStatistics'])->name('get-statistics');

            Route::prefix('{id}')->group(function () {
                Route::get('', [CommerceCustomerController::class, 'get'])->name('get');
                Route::get('transactions', [CommerceCustomerController::class, 'transactionPaginate'])->name('transaction-paginate');
                Route::delete('', [CommerceCustomerController::class, 'delete'])->name('delete')->block();
                Route::patch('', [CommerceCustomerController::class, 'update'])->name('update')->block();
            });
        });

        Route::prefix('commerce-payments')->name('commerce-payment.')->group(function () {
            Route::post('', [CommercePaymentController::class, 'create'])->name('create')->block();
            Route::get('', [CommercePaymentController::class, 'paginate'])->name('paginate');

            Route::prefix('{id}')->group(function () {
                Route::get('', [CommercePaymentController::class, 'get'])->name('get');
                Route::delete('', [CommercePaymentController::class, 'delete'])->name('delete')->block();
                Route::patch('', [CommercePaymentController::class, 'update'])->name('update')->block();
                Route::get('transactions', [CommercePaymentController::class, 'transactionPaginate'])->name('transaction-paginate');
                Route::post('disable', [CommercePaymentController::class, 'disable'])->name('disable')->block();
                Route::post('enable', [CommercePaymentController::class, 'enable'])->name('enable')->block();
            });
        });

        // *** CONTROL PANEL (admin) ***
        Route::prefix('admin')->middleware('permission:access_control_panel')->name('admin.')->group(function () {
            Route::prefix('statistics')->name('statistics.')->group(function () {
                Route::get('total-users', [StatisticsController::class, 'totalUsers'])->name('total-users');
                Route::get('total-earnings', [StatisticsController::class, 'totalEarnings'])->name('total-earnings');
                Route::get('pending-verification', [StatisticsController::class, 'pendingVerification'])->name('pending-verification');
                Route::get('pending-deposit', [StatisticsController::class, 'pendingDeposit'])->name('pending-deposit');
                Route::get('pending-withdrawal', [StatisticsController::class, 'pendingWithdrawal'])->name('pending-withdrawal');
                Route::get('registration-chart', [StatisticsController::class, 'registrationChart'])->name('registration-chart');
                Route::get('system-status', [StatisticsController::class, 'systemStatus'])->name('system-status');
                Route::get('latest-users', [StatisticsController::class, 'latestUsers'])->name('latest-users');
            });

            Route::prefix('users')->name('user.')->group(function () {
                Route::get('', [Admin\UserController::class, 'paginate'])->name('paginate');
                Route::post('deactivate', [Admin\UserController::class, 'batchDeactivate'])->name('batch-deactivate')->block();
                Route::post('activate', [Admin\UserController::class, 'batchActivate'])->name('batch-activate')->block();

                Route::prefix('{user}')->middleware('permission:manage_users')->group(function () {
                    Route::patch('', [Admin\UserController::class, 'update'])->name('update')->block();
                    Route::get('activities', [Admin\UserController::class, 'activityPaginate'])->name('activity-paginate');
                    Route::post('reset-password', [Admin\UserController::class, 'resetPassword'])->name('reset-password')->block();
                    Route::post('disable-two-factor', [Admin\UserController::class, 'disableTwoFactor'])->name('disable-two-factor')->block();
                    Route::post('reset-two-factor', [Admin\UserController::class, 'resetTwoFactor'])->name('reset-two-factor')->block();
                    Route::post('activate', [Admin\UserController::class, 'activate'])->name('activate')->block();
                    Route::post('deactivate', [Admin\UserController::class, 'deactivate'])->name('deactivate')->block();
                });
            });

            Route::prefix('user-verifications')->name('user-verification.')->group(function () {
                Route::get('addresses', [Admin\UserVerificationController::class, 'addressPaginate'])->name('address-paginate');
                Route::get('documents', [Admin\UserVerificationController::class, 'documentPaginate'])->name('document-paginate');

                Route::prefix('documents/{document}')->group(function () {
                    Route::post('approve', [Admin\UserVerificationController::class, 'approveDocument'])->name('approve-document')->block();
                    Route::post('reject', [Admin\UserVerificationController::class, 'rejectDocument'])->name('reject-document')->block();
                    Route::get('download', [Admin\UserVerificationController::class, 'downloadDocument'])->name('download-document');
                });

                Route::prefix('addresses/{address}')->group(function () {
                    Route::post('approve', [Admin\UserVerificationController::class, 'approveAddress'])->name('approve-address')->block();
                    Route::post('reject', [Admin\UserVerificationController::class, 'rejectAddress'])->name('reject-address')->block();
                });
            });

            Route::prefix('roles')->name('role.')->group(function () {
                Route::get('', [Admin\RoleController::class, 'paginate'])->name('paginate');
                Route::get('all', [Admin\RoleController::class, 'all'])->name('all');
                Route::post('', [Admin\RoleController::class, 'create'])->name('create')->block();

                Route::post('assign/{user}', [Admin\RoleController::class, 'assign'])->name('assign')->block();
                Route::get('permissions', [Admin\RoleController::class, 'getPermissions'])->name('get-permissions');

                Route::delete('{role}', [Admin\RoleController::class, 'delete'])->name('delete')->block();
                Route::put('{role}', [Admin\RoleController::class, 'update'])->name('update')->block();
            });

            Route::prefix('wallets')->name('wallet.')->group(function () {
                Route::post('', [Admin\WalletController::class, 'create'])->name('create')->block();
                Route::get('', [Admin\WalletController::class, 'paginate'])->name('paginate');
                Route::get('adapters', [Admin\WalletController::class, 'getAdapters'])->name('get-adapters');

                Route::prefix('{identifier}')->group(function () {
                    Route::delete('', [Admin\WalletController::class, 'delete'])->name('delete')->block();
                    Route::get('fee-address', [Admin\WalletController::class, 'getFeeAddress'])->name('get-fee-address');
                    Route::post('consolidate', [Admin\WalletController::class, 'consolidate'])->name('consolidate')->block();
                    Route::post('relay-transaction', [Admin\WalletController::class, 'relayTransaction'])->name('relay-transaction')->block();
                    Route::post('reset-webhook', [Admin\WalletController::class, 'resetWebhook'])->name('reset-webhook')->block();
                });
            });

            Route::prefix('withdrawal-fees')->name('withdrawal-fee.')->group(function () {
                Route::get('all', [Admin\WithdrawalFeeController::class, 'all'])->name('all');
                Route::patch('', [Admin\WithdrawalFeeController::class, 'update'])->name('update')->block();
            });

            Route::prefix('peer-fees')->name('peer-fee.')->group(function () {
                Route::get('all', [Admin\PeerFeeController::class, 'all'])->name('all');
                Route::patch('', [Admin\PeerFeeController::class, 'update'])->name('update')->block();
            });

            Route::prefix('commerce-fees')->name('commerce-fee.')->group(function () {
                Route::get('all', [Admin\CommerceFeeController::class, 'all'])->name('all');
                Route::patch('', [Admin\CommerceFeeController::class, 'update'])->name('update')->block();
            });

            Route::prefix('exchange-fees')->name('exchange-fee.')->group(function () {
                Route::get('all', [Admin\ExchangeFeeController::class, 'all'])->name('all');
                Route::patch('', [Admin\ExchangeFeeController::class, 'update'])->name('update')->block();
            });

            Route::prefix('transfer-records')->name('transfer-record.')->group(function () {
                Route::get('', [Admin\TransferRecordController::class, 'paginate'])->name('paginate');
                Route::delete('{record}/remove', [Admin\TransferRecordController::class, 'remove'])->name('remove')->middleware('two-factor')->block();
            });

            Route::prefix('payment-transactions')->name('payment-transaction.')->group(function () {
                Route::get('', [Admin\PaymentTransactionController::class, 'paginate'])->name('paginate');

                Route::prefix('{transaction}')->middleware('two-factor')->group(function () {
                    Route::post('complete-transfer', [Admin\PaymentTransactionController::class, 'completeTransfer'])->name('complete-transfer')->block();
                    Route::post('cancel-transfer', [Admin\PaymentTransactionController::class, 'cancelTransfer'])->name('cancel-transfer')->block();
                });
            });

            Route::prefix('supported-currencies')->name('supported-currency.')->group(function () {
                Route::get('available', [Admin\SupportedCurrencyController::class, 'available'])->name('available');
                Route::get('', [Admin\SupportedCurrencyController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\SupportedCurrencyController::class, 'create'])->name('create')->block();

                Route::prefix('{currency}')->group(function () {
                    Route::post('update-rate', [Admin\SupportedCurrencyController::class, 'updateRate'])->name('update-rate')->block();
                    Route::post('update-limit', [Admin\SupportedCurrencyController::class, 'updateLimit'])->name('update-limit')->block();
                    Route::post('make-default', [Admin\SupportedCurrencyController::class, 'makeDefault'])->name('make-default')->block();
                    Route::delete('', [Admin\SupportedCurrencyController::class, 'delete'])->name('delete')->block();
                });
            });

            Route::prefix('banks')->name('bank.')->group(function () {
                Route::post('', [Admin\BankController::class, 'create'])->name('create')->block();
                Route::get('', [Admin\BankController::class, 'paginate'])->name('paginate');
                Route::get('operating', [Admin\BankController::class, 'getOperatingBanks'])->name('get-operating-banks');

                Route::prefix('{bank}')->group(function () {
                    Route::post('set-logo', [Admin\BankController::class, 'setLogo'])->name('set-logo')->block();
                    Route::put('', [Admin\BankController::class, 'update'])->name('update')->block();
                    Route::delete('', [Admin\BankController::class, 'delete'])->name('delete')->block();
                });
            });

            Route::prefix('operating-countries')->name('operating-country.')->group(function () {
                Route::get('available', [Admin\OperatingCountryController::class, 'getAvailable'])->name('available');
                Route::get('', [Admin\OperatingCountryController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\OperatingCountryController::class, 'create'])->name('create')->block();

                Route::prefix('{country}')->group(function () {
                    Route::delete('', [Admin\OperatingCountryController::class, 'delete'])->name('delete')->block();
                });
            });

            Route::prefix('bank-accounts')->name('bank-account.')->group(function () {
                Route::get('', [Admin\BankAccountController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\BankAccountController::class, 'create'])->name('create')->block();

                Route::prefix('{account}')->group(function () {
                    Route::delete('', [Admin\BankAccountController::class, 'delete'])->name('delete')->block();
                });
            });

            Route::prefix('exchange-trades')->name('exchange-trade.')->group(function () {
                Route::get('', [Admin\ExchangeTradeController::class, 'paginate'])->name('paginate');

                Route::prefix('{trade}')->group(function () {
                    Route::patch('complete-pending', [Admin\ExchangeTradeController::class, 'completePending'])->name('complete-pending')->block();
                    Route::patch('cancel-pending', [Admin\ExchangeTradeController::class, 'cancelPending'])->name('cancel-pending')->block();
                });
            });

            Route::prefix('stake-plans')->name('stake-plan.')->group(function () {
                Route::post('', [Admin\StakePlanController::class, 'create'])->name('create')->block();
                Route::get('', [Admin\StakePlanController::class, 'paginate'])->name('paginate');

                Route::prefix('{plan}')->group(function () {
                    Route::delete('', [Admin\StakePlanController::class, 'delete'])->name('delete')->block();
                    Route::put('', [Admin\StakePlanController::class, 'update'])->name('update')->block();
                });
            });

            Route::prefix('stakes')->name('stake.')->group(function () {
                Route::get('', [Admin\StakeController::class, 'paginate'])->name('paginate');
                Route::get('statistics', [Admin\StakeController::class, 'getStatistics'])->name('get-statistics');

                Route::prefix('{stake}')->group(function () {
                    Route::patch('redeem', [Admin\StakeController::class, 'redeem'])->name('redeem')->block();
                });
            });

            Route::prefix('peer-payment-categories')->name('peer-payment-category.')->group(function () {
                Route::get('all', [Admin\PeerPaymentCategoryController::class, 'all'])->name('all');
                Route::get('', [Admin\PeerPaymentCategoryController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\PeerPaymentCategoryController::class, 'create'])->name('create')->block();

                Route::prefix('{category}')->group(function () {
                    Route::delete('', [Admin\PeerPaymentCategoryController::class, 'delete'])->name('delete')->block();
                    Route::put('', [Admin\PeerPaymentCategoryController::class, 'update'])->name('update')->block();
                });
            });

            Route::prefix('peer-payment-methods')->name('peer-payment-method.')->group(function () {
                Route::get('', [Admin\PeerPaymentMethodController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\PeerPaymentMethodController::class, 'create'])->name('create')->block();

                Route::prefix('{method}')->group(function () {
                    Route::delete('', [Admin\PeerPaymentMethodController::class, 'delete'])->name('delete')->block();
                    Route::put('', [Admin\PeerPaymentMethodController::class, 'update'])->name('update')->block();
                });
            });

            Route::prefix('peer-trades')->name('peer-trade.')->group(function () {
                Route::get('', [Admin\PeerTradeController::class, 'paginate'])->name('paginate');
                Route::get('statistics', [Admin\PeerTradeController::class, 'getStatistics'])->name('get-statistics');

                Route::prefix('{trade}')->group(function () {
                    Route::post('join', [Admin\PeerTradeController::class, 'join'])->name('join')->block();
                });
            });

            Route::prefix('giftcards')->name('giftcard.')->group(function () {
                Route::get('', [Admin\GiftcardController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\GiftcardController::class, 'create'])->name('create')->block();

                Route::prefix('{giftcard}')->group(function () {
                    Route::post('upload-thumbnail', [Admin\GiftcardController::class, 'uploadThumbnail'])->name('upload-thumbnail')->block();
                    Route::put('', [Admin\GiftcardController::class, 'update'])->name('update')->block();
                    Route::delete('', [Admin\GiftcardController::class, 'delete'])->name('delete')->block();

                    Route::prefix('contents')->name('content.')->group(function () {
                        Route::delete('{content}', [Admin\GiftcardController::class, 'deleteContent'])->name('delete')->block();
                        Route::get('', [Admin\GiftcardController::class, 'contentPaginate'])->name('paginate');
                        Route::post('', [Admin\GiftcardController::class, 'createContent'])->name('create')->block();
                    });
                });
            });

            Route::prefix('giftcard-contents')->name('giftcard-content.')->group(function () {
                Route::get('purchased', [Admin\GiftcardContentController::class, 'purchasedPaginate'])->name('purchased-paginate');
            });

            Route::prefix('giftcard-brands')->name('giftcard-brand.')->group(function () {
                Route::get('all', [Admin\GiftcardBrandController::class, 'all'])->name('all');
                Route::get('', [Admin\GiftcardBrandController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\GiftcardBrandController::class, 'create'])->name('create')->block();

                Route::prefix('{brand}')->group(function () {
                    Route::delete('', [Admin\GiftcardBrandController::class, 'delete'])->name('delete')->block();
                    Route::put('', [Admin\GiftcardBrandController::class, 'update'])->name('update')->block();
                });
            });

            Route::prefix('commerce-transactions')->name('commerce-transaction.')->group(function () {
                Route::get('', [Admin\CommerceTransactionController::class, 'paginate'])->name('paginate');
                Route::get('status-statistics', [Admin\CommerceTransactionController::class, 'getStatusStatistics'])->name('get-status-statistics');
            });

            Route::prefix('locale')->name('locale.')->group(function () {
                Route::get('list', [Admin\LocaleController::class, 'get'])->name('get');
                Route::post('remove', [Admin\LocaleController::class, 'remove'])->name('remove')->block();
                Route::post('add', [Admin\LocaleController::class, 'add'])->name('add')->block();
                Route::post('import', [Admin\LocaleController::class, 'import'])->name('import')->block();

                Route::prefix('groups/{group}')->name('group.')->group(function () {
                    Route::get('', [Admin\LocaleController::class, 'getGroup'])->name('get');
                    Route::patch('', [Admin\LocaleController::class, 'updateGroup'])->name('update')->middleware('restrict.demo')->block();
                    Route::post('export', [Admin\LocaleController::class, 'exportGroup'])->name('export')->middleware('restrict.demo')->block();
                });
            });

            Route::prefix('theme')->name('theme.')->group(function () {
                Route::post('set-mode', [Admin\ThemeController::class, 'setMode'])->name('set-mode')->block();
                Route::post('set-direction', [Admin\ThemeController::class, 'setDirection'])->name('set-direction')->middleware('restrict.demo')->block();
                Route::post('set-color', [Admin\ThemeController::class, 'setColor'])->name('set-color')->block();
            });

            Route::prefix('brand')->name('brand.')->group(function () {
                Route::get('', [Admin\BrandController::class, 'get'])->name('get');
                Route::post('upload-logo', [Admin\BrandController::class, 'uploadLogo'])->name('upload-logo')->middleware('restrict.demo')->block();
                Route::post('upload-favicon', [Admin\BrandController::class, 'uploadFavicon'])->name('upload-favicon')->middleware('restrict.demo')->block();
                Route::patch('links', [Admin\BrandController::class, 'updateLinks'])->name('update-links')->middleware('restrict.demo')->block();
            });

            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('general', [Admin\SettingsController::class, 'getGeneral'])->name('get-general');
                Route::patch('general', [Admin\SettingsController::class, 'updateGeneral'])->name('update-general')->middleware('restrict.demo')->block();

                Route::get('service', [Admin\SettingsController::class, 'getService'])->name('get-service');
                Route::patch('service', [Admin\SettingsController::class, 'updateService'])->name('update-service')->middleware('restrict.demo')->block();
            });

            Route::prefix('modules')->name('module.')->group(function () {
                Route::get('', [Admin\ModuleController::class, 'paginate'])->name('paginate');
                Route::get('operators', [Admin\ModuleController::class, 'getOperators'])->name('get-operators');

                Route::prefix('{module}')->group(function () {
                    Route::patch('disable', [Admin\ModuleController::class, 'disable'])->name('disable')->block();
                    Route::post('set-operator', [Admin\ModuleController::class, 'setOperator'])->name('set-operator')->block();
                    Route::patch('enable', [Admin\ModuleController::class, 'enable'])->name('enable')->block();
                });
            });

            Route::prefix('grids')->name('grid.')->group(function () {
                Route::get('', [Admin\GridController::class, 'paginate'])->name('paginate');

                Route::prefix('{grid}')->group(function () {
                    Route::patch('disable', [Admin\GridController::class, 'disable'])->name('disable')->block();
                    Route::patch('enable', [Admin\GridController::class, 'enable'])->name('enable')->block();
                });
            });

            Route::prefix('feature-limits')->name('feature-limit.')->group(function () {
                Route::get('all', [Admin\FeatureLimitController::class, 'all'])->name('all');
                Route::patch('', [Admin\FeatureLimitController::class, 'update'])->name('update')->block();

                Route::get('settings', [Admin\FeatureLimitController::class, 'getSettings'])->name('get-settings');
                Route::patch('settings', [Admin\FeatureLimitController::class, 'updateSettings'])->name('update-settings')->block();
            });

            Route::prefix('required-documents')->name('required-document.')->group(function () {
                Route::get('', [Admin\RequiredDocumentController::class, 'paginate'])->name('paginate');
                Route::post('', [Admin\RequiredDocumentController::class, 'create'])->name('create')->block();

                Route::prefix('{document}')->group(function () {
                    Route::delete('', [Admin\RequiredDocumentController::class, 'delete'])->name('delete')->block();
                    Route::put('', [Admin\RequiredDocumentController::class, 'update'])->name('update')->block();
                });
            });

            Route::prefix('system-logs')->name('system-logs.')->group(function () {
                Route::get('', [Admin\SystemLogController::class, 'paginate'])->name('paginate');
                Route::post('mark-all-as-seen', [Admin\SystemLogController::class, 'markAllAsSeen'])->name('mark-all-as-seen')->block();

                Route::prefix('{log}')->group(function () {
                    Route::post('mark-as-seen', [Admin\SystemLogController::class, 'markAsSeen'])->name('mark-as-seen')->block();
                });
            });
        });
    });

    Route::prefix('global')->name('global.')->group(function () {
        Route::get('wallets', [GlobalController::class, 'wallets'])->name('wallets');
        Route::get('supported-currencies', [GlobalController::class, 'supportedCurrencies'])->name('supported-currencies');
        Route::get('countries', [GlobalController::class, 'countries'])->name('countries');
        Route::get('operating-countries', [GlobalController::class, 'operatingCountries'])->name('operating-countries');
    });

    // IP Address Data
    Route::prefix('ip')->name('ip.')->group(function () {
        Route::post('info', [AppController::class, 'ipInfo'])->name('info');
    });

    // Locale Routes
    Route::prefix('locale')->name('locale.')->group(function () {
        Route::post('set', [LocaleController::class, 'set'])->name('set');
        Route::post('get', [LocaleController::class, 'get'])->name('get');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('brand', [SettingsController::class, 'getBrand'])->name('brand');
        Route::get('theme', [SettingsController::class, 'getTheme'])->name('theme');
    });

    Route::prefix('page')->name('page.')->group(function () {
        Route::prefix('commerce-payments/{payment}')->name('commerce-payment.')->group(function () {
            Route::get('', [Page\CommercePaymentController::class, 'get'])->name('get')->middleware('can:viewPage,payment');
            Route::post('customers', [Page\CommercePaymentController::class, 'createCustomer'])->name('create-customer')->middleware('can:viewPage,payment')->block();

            Route::prefix('transactions/{id}')->group(function () {
                Route::get('', [Page\CommercePaymentController::class, 'getTransaction'])->name('get-transaction');
                Route::patch('', [Page\CommercePaymentController::class, 'updateTransaction'])->name('update-transaction')->middleware('can:viewPage,payment')->block();
            });

            Route::prefix('customers/{email}')->middleware('can:viewPage,payment')->group(function () {
                Route::get('', [Page\CommercePaymentController::class, 'getCustomer'])->name('get-customer');
                Route::post('transaction', [Page\CommercePaymentController::class, 'createTransaction'])->name('create-transaction')->block();
                Route::get('transaction', [Page\CommercePaymentController::class, 'getActiveTransaction'])->name('get-active-transaction');
            });
        });
    });
});

Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::prefix('coin/{identifier}')->name('coin.')->group(function () {
        Route::post('transaction', [CoinController::class, 'handleTransaction'])->name('transaction');
    });
});

Route::prefix('gateway/{order}')->name('gateway.')->group(function () {
    Route::get('callback', [GatewayController::class, 'handleReturn'])->name('return');
    Route::post('callback', [GatewayController::class, 'handleNotify'])->name('notify');
});

Route::prefix('page')->name('page.')->group(function () {
    Route::prefix('commerce-payments/{payment}')->middleware('can:viewPage,payment')->name('commerce-payment.')->group(function () {
        Route::get('', [Page\CommercePaymentController::class, 'view'])->name('view');
    });
});

Route::name('installer.')->group(function () {
    Route::get('installer', [InstallerController::class, 'view']);
    Route::post('installer/register', [InstallerController::class, 'register'])->name('register');
    Route::post('installer/install', [InstallerController::class, 'install'])->name('install');
});

Route::middleware([AddCspHeaders::class, 'installer.redirect'])->group(function () {
    Route::get('admin/{any?}', [AppController::class, 'admin'])->where('any', '.*')->name('admin');
    Route::get('{any?}', [AppController::class, 'main'])->where('any', '.*')->name('main');
});
