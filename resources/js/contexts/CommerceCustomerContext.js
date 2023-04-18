import React, {createContext} from "react";
import PropTypes from "prop-types";

const initialState = {
    fetchCustomer: () => {}
};

const CommerceCustomerContext = createContext(initialState);

const CommerceCustomerProvider = ({customer, fetchCustomer, children}) => {
    return (
        <CommerceCustomerContext.Provider value={{customer, fetchCustomer}}>
            {children}
        </CommerceCustomerContext.Provider>
    );
};

CommerceCustomerProvider.propTypes = {
    customer: PropTypes.object,
    fetchCustomer: PropTypes.func
};

export {CommerceCustomerProvider};
export default CommerceCustomerContext;
