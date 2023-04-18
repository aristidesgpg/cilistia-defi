import React, {createContext} from "react";
import PropTypes from "prop-types";

const initialState = {
    fetchPayment: () => {}
};

const CommercePaymentContext = createContext(initialState);

const CommercePaymentProvider = ({payment, fetchPayment, children}) => {
    return (
        <CommercePaymentContext.Provider value={{payment, fetchPayment}}>
            {children}
        </CommercePaymentContext.Provider>
    );
};

CommercePaymentProvider.propTypes = {
    payment: PropTypes.object,
    fetchPayment: PropTypes.func
};

export {CommercePaymentProvider};
export default CommercePaymentContext;
