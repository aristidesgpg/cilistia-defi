import PropTypes from "prop-types";
import React, {useEffect} from "react";
import rtlPlugin from "stylis-plugin-rtl";
import createCache from "@emotion/cache";
import {CacheProvider} from "@emotion/react";
import useSettings from "hooks/useSettings";

/* eslint-disable no-undef */

function EmotionCacheProvider({children}) {
    const {themeDirection} = useSettings();
    const isRtl = themeDirection === "rtl";

    useEffect(() => {
        document.dir = themeDirection;
    }, [themeDirection]);

    const cacheRtl = createCache({
        key: isRtl ? "rtl" : "css",
        stylisPlugins: isRtl ? [rtlPlugin] : [],
        nonce: __webpack_nonce__
    });

    return <CacheProvider value={cacheRtl}>{children}</CacheProvider>;
}

EmotionCacheProvider.propTypes = {children: PropTypes.node};

export default EmotionCacheProvider;
