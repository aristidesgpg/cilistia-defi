import ReactDOM from "react-dom";
import {Provider} from "react-redux";
import store from "redux/store/commercePayment";
import CommercePayment from "layouts/CommercePayment";
import Localization from "core/localization";
import Bootstrap from "core/bootstrap";
import React from "react";

ReactDOM.render(
    <Provider store={store}>
        <Localization>
            <Bootstrap>
                <CommercePayment />
            </Bootstrap>
        </Localization>
    </Provider>,
    document.getElementById("root")
);
