import axios from "axios";
import context from "core/context";
import React, {useEffect, useMemo, useState} from "react";
import {
    assign,
    castArray,
    first,
    flatten,
    forOwn,
    get,
    isEmpty,
    isFunction,
    isString,
    values
} from "lodash";
import {getNamePath, mountHandler, notify, useVar} from "utils";
import {Button} from "@mui/material";
import {FormattedMessage} from "react-intl";
import Qs from "qs";

const csrfToken = context.csrfToken;
axios.defaults.headers.common["Content-Type"] = "application/json";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;

export {axios, csrfToken};

export default class Http {
    constructor() {
        this.request = axios.create();
        this.resetCancelToken();
    }

    resetCancelToken() {
        this.source = axios.CancelToken.source();
        this.request.defaults.cancelToken = this.source.token;
    }

    cancel(message) {
        this.source.cancel(message);
        this.resetCancelToken();
    }

    isCancel(error) {
        return axios.isCancel(error);
    }
}

const refreshPage = (snackKey) => {
    notify.close(snackKey);
    window.location.reload();
};

const triggerUnavailable = (data) => {
    notify.warning(data.message, {
        persist: true,
        anchorOrigin: {vertical: "top", horizontal: "center"},
        action: (key) => (
            <Button
                variant="contained"
                size="small"
                onClick={() => refreshPage(key)}
                sx={{borderRadius: '5px'}}
                color="warning">
                <FormattedMessage defaultMessage="Refresh" />
            </Button>
        )
    });
};

const isUnavailable = (status) => {
    return [401, 419].includes(status);
};

/**
 * Request hook
 *
 * @returns {[axios, boolean]}
 */
export const useRequest = () => {
    const [loading, setLoading] = useState(false);
    const service = useVar(() => new Http());

    useEffect(() => {
        const handler = mountHandler();
        const interceptors = service.request.interceptors;

        const requestInterceptor = interceptors.request.use((config) => {
            config.paramsSerializer = (params) => {
                return Qs.stringify(params, {
                    encode: false,
                    arrayFormat: "indices"
                });
            };

            handler.execute(() => setLoading(true));

            return config;
        });

        const responseInterceptor = interceptors.response.use(
            ({data}) => {
                handler.execute(() => setLoading(false));

                return data;
            },
            (error) => {
                handler.execute(() => setLoading(false));

                error.canceled = axios.isCancel(error);

                return Promise.reject(error);
            }
        );

        return () => {
            service.cancel();
            interceptors.response.eject(responseInterceptor);
            interceptors.request.eject(requestInterceptor);
            handler.unmount();
        };
    }, [service]);

    return [service.request, loading];
};

/**
 * Form request hook
 *
 * @param form
 * @returns {[axios, boolean]}
 */
export const useFormRequest = (form) => {
    const [loading, setLoading] = useState(false);
    const service = useVar(() => new Http());

    useEffect(() => {
        const handler = mountHandler();
        const interceptors = service.request.interceptors;

        const requestInterceptor = interceptors.request.use((config) => {
            config.paramsSerializer = (params) => {
                return Qs.stringify(params, {
                    encode: false,
                    arrayFormat: "indices"
                });
            };

            handler.execute(() => setLoading(true));

            return config;
        });

        const responseInterceptor = interceptors.response.use(
            ({data}) => {
                handler.execute(() => setLoading(false));

                return data;
            },
            (error) => {
                handler.execute(() => setLoading(false));

                error.canceled = axios.isCancel(error);
                error.form = form;

                return Promise.reject(error);
            }
        );

        return () => {
            service.cancel();
            interceptors.response.eject(responseInterceptor);
            interceptors.request.eject(requestInterceptor);
            handler.unmount();
        };
    }, [service, form]);

    return [service.request, loading];
};

export function useUploadRequest() {
    const [loading, setLoading] = useState(false);
    const service = useVar(() => new Http());

    useEffect(() => {
        const handler = mountHandler();
        const interceptors = service.request.interceptors;

        const requestInterceptor = interceptors.request.use((config) => {
            handler.execute(() => {
                setLoading(true);
            });
            return config;
        });

        const responseInterceptor = interceptors.response.use(
            (response) => {
                handler.execute(() => setLoading(false));

                return response;
            },
            (error) => {
                handler.execute(() => {
                    setLoading(false);

                    if (error.response) {
                        const {status, data} = error.response;

                        if (isUnavailable(status)) {
                            triggerUnavailable(data);
                        }
                    }
                });

                error.canceled = axios.isCancel(error);

                return Promise.reject(error);
            }
        );
        return () => {
            handler.unmount();
            interceptors.request.eject(requestInterceptor);
            interceptors.response.eject(responseInterceptor);
            service.cancel();
        };
    }, [service]);

    const request = useMemo(() => {
        return function (options) {
            const form = new FormData();
            form.set(options.filename, options.file);

            forOwn(options.data, (v, k) => {
                form.append(k, v);
            });

            service.request
                .post(options.action, form, {
                    headers: assign(
                        {"Content-Type": "multipart/form-data"},
                        options.headers
                    ),

                    onUploadProgress(progress) {
                        const {onProgress} = options;

                        if (isFunction(onProgress)) {
                            const {loaded, total} = progress;
                            const percent = Math.round((loaded * 100) / total);
                            return onProgress({percent});
                        }
                    }
                })
                .then((response) => {
                    const {onSuccess} = options;

                    if (isFunction(onSuccess)) {
                        const {data, request} = response;
                        return onSuccess(data, request);
                    }
                })
                .catch((error) => {
                    const {onError} = options;

                    if (!error.canceled && error.response) {
                        const data = error.response.data;

                        if (isFunction(onError)) {
                            return onError(error, data);
                        }
                    }
                });
        };
    }, [service]);

    return [request, loading];
}

export function notifyError(error) {
    const {errors, message} = get(error, "response.data", {});

    if (!isEmpty(errors)) {
        flattenValues(errors).forEach((error) => {
            notify.error(error);
        });
    } else if (isString(message)) {
        notify.error(message);
    }
}

export function errorHandler(callback) {
    return (error) => {
        if (error.response) {
            const {status, data} = error.response;
            const form = error.form;

            if (isUnavailable(status)) {
                triggerUnavailable(data);
            } else {
                const {errors, message} = data;

                if (!isEmpty(errors)) {
                    const fields = [];

                    forOwn(errors, (errorList, key) => {
                        const name = getNamePath(key);
                        fields.push({name, errors: errorList});

                        if (!form?.getFieldValue(name)) {
                            castArray(errorList).forEach((e) => {
                                return notify.error(e);
                            });
                        }
                    });

                    form?.setFields(fields);
                    form?.scrollToField(first(fields).name);
                } else if (isString(message)) {
                    notify.error(message);
                }
            }
        }

        if (!axios.isCancel(error)) {
            return callback?.(error);
        }
    };
}

export function thunkRequest() {
    const service = new Http();

    service.request.interceptors.response.use(
        (response) => {
            return response.data;
        },
        (error) => {
            if (error.response) {
                const {status, data} = error.response;

                if (isUnavailable(status)) {
                    triggerUnavailable(data);
                } else if (data.message) {
                    notify.error(data.message);
                }
            }

            return Promise.reject(error);
        }
    );

    return service.request;
}

const flattenValues = (data) => flatten(values(data));
