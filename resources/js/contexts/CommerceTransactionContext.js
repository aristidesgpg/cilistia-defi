import React, {createContext} from "react";
import PropTypes from "prop-types";

const initialState = {
    fetchTransaction: () => {}
};

const CommerceTransactionContext = createContext(initialState);

const CommerceTransactionProvider = ({
    transaction,
    fetchTransaction,
    children
}) => {
    return (
        <CommerceTransactionContext.Provider
            value={{transaction, fetchTransaction}}>
            {children}
        </CommerceTransactionContext.Provider>
    );
};

CommerceTransactionProvider.propTypes = {
    transaction: PropTypes.object,
    fetchTransaction: PropTypes.func
};

export {CommerceTransactionProvider};
export default CommerceTransactionContext;
