import dayjs from "utils/dayjs";
import React, {createContext, useState} from "react";

const initialState = {
    setFrom: () => null,
    setTo: () => null
};

const PeriodContext = createContext(initialState);

const PeriodProvider = ({children}) => {
    const [from, setFrom] = useState(() => dayjs().startOf("month"));
    const [to, setTo] = useState(() => dayjs());

    return (
        <PeriodContext.Provider value={{from, setFrom, to, setTo}}>
            {children}
        </PeriodContext.Provider>
    );
};

export {PeriodProvider};
export default PeriodContext;
