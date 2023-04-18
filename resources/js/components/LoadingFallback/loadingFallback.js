import React, {useEffect, useState} from "react";
import {isEmpty, isFunction} from "lodash";
import LoadingScreen from "components/LoadingScreen";
import Spin from "components/Spin";
import {FormattedMessage} from "react-intl";
import Result from "components/Result";
import {EmptyIllustration} from "assets/index";
import PropTypes from "prop-types";

const LoadingFallback = ({
    fallback,
    content,
    children,
    loading = false,
    compact = false,
    thickness = 3.6,
    size = 40,
    fallbackTitle = (
        <FormattedMessage defaultMessage="Oops! Nothing is here." />
    ),
    fallbackDescription = (
        <FormattedMessage defaultMessage="Please, check back later." />
    ),
    fallbackIcon = EmptyIllustration,
    fallbackIconSize = 240
}) => {
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    if (isEmpty(content)) {
        if (loading) {
            return <LoadingScreen size={size} thickness={thickness} />;
        } else {
            if (!mounted) return null;

            return (
                fallback ?? (
                    <Result
                        title={fallbackTitle}
                        description={fallbackDescription}
                        iconSize={fallbackIconSize}
                        icon={fallbackIcon}
                    />
                )
            );
        }
    }

    return (
        <Spin
            spinning={loading}
            thickness={thickness}
            compact={compact}
            size={size}>
            {isFunction(children) ? children(content) : children}
        </Spin>
    );
};

LoadingFallback.propTypes = {
    fallback: PropTypes.oneOfType([PropTypes.node, PropTypes.bool]),
    content: PropTypes.any,
    loading: PropTypes.bool.isRequired,
    thickness: PropTypes.number,
    size: PropTypes.number,
    children: PropTypes.oneOfType([PropTypes.func, PropTypes.node]),
    fallbackIconSize: PropTypes.number
};

export default LoadingFallback;
