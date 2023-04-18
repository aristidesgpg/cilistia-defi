import React from "react";
import ReactQuill from "react-quill";
import {styled} from "@mui/material/styles";
import "react-quill/dist/quill.snow.css";

export default (props) => {
    return <StyledReactQuill {...props} />;
};

const StyledReactQuill = styled((props) => {
    return <ReactQuill theme="snow" {...props} />;
})(({theme}) => ({
    borderRadius: theme.shape.borderRadius,
    border: `solid 1px ${theme.palette.grey[500_32]}`,
    "&:focus-within": {borderWidth: "2px"},
    "& .ql-container.ql-snow": {
        ...theme.typography.body1,
        borderColor: "transparent",
        fontFamily: theme.typography.fontFamily
    },
    "& .ql-editor": {
        minHeight: 200,
        maxHeight: 640,
        "&.ql-blank::before": {
            fontStyle: "normal",
            color: theme.palette.text.disabled
        },
        "& pre.ql-syntax": {
            ...theme.typography.body2,
            borderRadius: theme.shape.borderRadius,
            backgroundColor: theme.palette.grey[900],
            padding: theme.spacing(2)
        }
    },
    ".ql-snow .ql-tooltip": {
        ...theme.typography.body2,
        border: "none",
        color: theme.palette.text.primary,
        borderRadius: theme.shape.borderRadius,
        backgroundColor: theme.palette.background.paper,
        boxShadow: theme.customShadows.z8,
        zIndex: 9,
        "& a": {
            fontWeight: theme.typography.fontWeightBold,
            color: theme.palette.text.primary
        },
        "& input[type=text]": {
            backgroundColor: "transparent",
            border: `solid 1px ${theme.palette.grey[500_32]}`,
            color: theme.palette.text.primary,
            "&::placeholder": {
                ...theme.typography.body2,
                color: theme.palette.text.disabled
            },
            "&:focus-visible": {outline: "none"}
        }
    },
    "& .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow .ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button:focus .ql-fill, .ql-snow .ql-toolbar button:focus .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill, .ql-snow .ql-toolbar button.ql-active .ql-fill, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-fill, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-fill, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-fill, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-fill, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-fill, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-fill, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-fill, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-fill, .ql-snow.ql-toolbar button:hover .ql-stroke.ql-fill, .ql-snow .ql-toolbar button:hover .ql-stroke.ql-fill, .ql-snow.ql-toolbar button:focus .ql-stroke.ql-fill, .ql-snow .ql-toolbar button:focus .ql-stroke.ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-stroke.ql-fill, .ql-snow .ql-toolbar button.ql-active .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-stroke.ql-fill":
        {fill: theme.palette.primary.main},
    "& .ql-snow.ql-toolbar button:hover, .ql-snow .ql-toolbar button:hover, .ql-snow.ql-toolbar button:focus, .ql-snow .ql-toolbar button:focus, .ql-snow.ql-toolbar button.ql-active, .ql-snow .ql-toolbar button.ql-active, .ql-snow.ql-toolbar .ql-picker-label:hover, .ql-snow .ql-toolbar .ql-picker-label:hover, .ql-snow.ql-toolbar .ql-picker-label.ql-active, .ql-snow .ql-toolbar .ql-picker-label.ql-active, .ql-snow.ql-toolbar .ql-picker-item:hover, .ql-snow .ql-toolbar .ql-picker-item:hover, .ql-snow.ql-toolbar .ql-picker-item.ql-selected, .ql-snow .ql-toolbar .ql-picker-item.ql-selected":
        {color: theme.palette.primary.main},
    "& .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow .ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button:focus .ql-stroke, .ql-snow .ql-toolbar button:focus .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke, .ql-snow .ql-toolbar button.ql-active .ql-stroke, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-stroke, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-stroke, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-stroke, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-stroke, .ql-snow.ql-toolbar button:hover .ql-stroke-miter, .ql-snow .ql-toolbar button:hover .ql-stroke-miter, .ql-snow.ql-toolbar button:focus .ql-stroke-miter, .ql-snow .ql-toolbar button:focus .ql-stroke-miter, .ql-snow.ql-toolbar button.ql-active .ql-stroke-miter, .ql-snow .ql-toolbar button.ql-active .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-stroke-miter":
        {stroke: theme.palette.primary.main},
    "& .ql-picker, .ql-picker-options, .ql-picker-item, .ql-picker-label, button":
        {"&:focus": {outline: "none"}},
    "& .ql-snow .ql-stroke": {
        stroke: theme.palette.text.primary
    },
    "& .ql-snow .ql-fill, .ql-snow .ql-stroke.ql-fill": {
        fill: theme.palette.text.primary
    },
    "& .ql-toolbar.ql-snow": {
        border: "none",
        borderBottom: `solid 1px ${theme.palette.grey[500_32]}`,
        "& .ql-formats": {
            "&:not(:last-of-type)": {
                marginRight: theme.spacing(2)
            }
        },
        "& button": {
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            padding: 0,
            borderRadius: 4,
            color: theme.palette.text.primary
        },
        "& button svg, span svg": {
            width: 20,
            height: 20
        },
        "& .ql-picker-label": {
            ...theme.typography.subtitle2,
            color: theme.palette.text.primary,
            "& .ql-stroke": {
                stroke: theme.palette.text.primary
            }
        },
        "& .ql-picker-label svg": {
            ...(theme.direction === "rtl" && {
                right: "0 !important",
                left: "auto !important"
            })
        },
        "& .ql-color,& .ql-background,& .ql-align ": {
            "& .ql-picker-label": {
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                padding: 0
            }
        },
        "& .ql-expanded": {
            "& .ql-picker-label": {
                color: theme.palette.text.disabled,
                backgroundColor: theme.palette.action.focus,
                borderColor: "transparent !important",
                borderRadius: 4,
                "& .ql-stroke": {stroke: theme.palette.text.disabled}
            },
            "& .ql-picker-options": {
                boxShadow: theme.customShadows.z20,
                backgroundColor: theme.palette.background.paper,
                borderRadius: theme.shape.borderRadius,
                border: "none",
                overflow: "auto",
                maxHeight: 200,
                padding: 0,
                marginTop: 4
            },
            "& .ql-picker-item": {
                color: theme.palette.text.primary
            },
            "&.ql-align": {
                "& .ql-picker-options": {padding: 0, display: "flex"},
                "& .ql-picker-item": {
                    display: "flex",
                    justifyContent: "center",
                    alignItems: "center",
                    width: 32,
                    height: 32
                }
            },
            "&.ql-color, &.ql-background": {
                "& .ql-picker-options": {padding: 8},
                "& .ql-picker-item": {
                    borderRadius: 4,
                    margin: 3,
                    "&.ql-selected": {border: "solid 1px black"}
                }
            },
            "&.ql-font, &.ql-size, &.ql-header": {
                "& .ql-picker-options": {
                    padding: theme.spacing(1, 0)
                },
                "& .ql-picker-item": {
                    padding: theme.spacing(0.5, 1.5)
                }
            }
        }
    }
}));
