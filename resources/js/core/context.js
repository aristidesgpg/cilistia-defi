import {createContext} from "react";

export const AppContext = createContext(window?.__APP__);

export default window?.__APP__ ?? {};
