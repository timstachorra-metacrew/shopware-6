const { Application } = Shopware;
const ApiService = Shopware.Classes.ApiService;

class HeidelPaymentService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'heidelpay') {
        super(httpClient, loginService, apiEndpoint);
    }

    fetchPaymentDetails(transaction) {
        const apiRoute = `_action/${this.getApiBasePath()}/transaction/${transaction}/details`;

        return this.httpClient.get(
            apiRoute,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    chargeTransaction(transaction, payment, amount) {
        const apiRoute = `_action/${this.getApiBasePath()}/transaction/${transaction}/charge/${amount}`;

        return this.httpClient.get(
            apiRoute,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    refundTransaction(transaction, charge, amount) {
        const apiRoute = `_action/${this.getApiBasePath()}/transaction/${transaction}/refund/${charge}/${amount}`;

        return this.httpClient.get(
            apiRoute,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    ship(transaction) {
        const apiRoute = `_action/${this.getApiBasePath()}/transaction/${transaction}/ship`;

        return this.httpClient.get(
            apiRoute,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

Application.addServiceProvider('HeidelPaymentService', (container) => {
    const initContainer = Application.getContainer('init');

    return new HeidelPaymentService(initContainer.httpClient, container.loginService);
});

