import React from "react";
import {Box} from "@mui/material";
import {useUniqueId} from "hooks/useUniqueId";

const EmptyContent = ({...other}) => {
    const getUniqueId = useUniqueId("empty_");

    return (
        <Box {...other}>
            <svg
                viewBox="0 0 327 240"
                xmlns="http://www.w3.org/2000/svg"
                width="100%"
                height="100%">
                <defs>
                    <linearGradient
                        id={getUniqueId("b")}
                        x1="19.496%"
                        x2="77.479%"
                        y1="70.898%"
                        y2="18.101%">
                        <stop offset={0} stopColor="#919eab" />
                        <stop offset={1} stopColor="#919eab" stopOpacity={0} />
                    </linearGradient>
                    <rect
                        id={getUniqueId("a")}
                        height={140}
                        rx={12}
                        width={172}
                    />
                    <mask id={getUniqueId("c")} fill="#fff">
                        <use
                            fillRule="evenodd"
                            xlinkHref={`#${getUniqueId("a")}`}
                        />
                    </mask>
                </defs>
                <g fill="none" fillRule="evenodd">
                    <path
                        d="M0 132.52c0 27.639 10.182 52.824 26.936 71.858C46.157 226.22 74.03 239.954 105.098 240c13.579.02 26.562-2.591 38.487-7.358 6.167-2.465 13.068-2.182 19.04.739a52.548 52.548 0 0023.134 5.34c3.421 0 6.772-.33 10.014-.955 9.252-1.78 17.671-5.994 24.586-11.96 4.345-3.752 9.91-5.638 15.599-5.631h.096c18.795 0 36.254-5.822 50.748-15.797 12.958-8.907 23.54-21.131 30.59-35.484C323.54 156.387 327 142.26 327 127.305c0-51.287-40.721-92.87-90.946-92.87-5.12 0-10.136.442-15.03 1.266C208.066 14.28 184.86 0 158.39 0c-11.177 0-21.772 2.545-31.256 7.107-9.02 4.325-17.039 10.47-23.599 17.96-22.275.362-42.871 7.793-59.729 20.194C17.271 64.771 0 96.588 0 132.521z"
                        fill={`url(#${getUniqueId("b")})`}
                        opacity={0.2}
                    />
                    <g fillRule="nonzero">
                        <path
                            d="M190.605 29.05a8.5 8.5 0 01-6.031-2.494c-3.326-3.326-3.326-8.737 0-12.062 3.324-3.325 8.736-3.326 12.061 0 3.326 3.326 3.326 8.737 0 12.062a8.5 8.5 0 01-6.03 2.494zm0-11.17a2.64 2.64 0 00-1.873.773 2.65 2.65 0 000 3.744 2.65 2.65 0 003.744 0 2.65 2.65 0 000-3.744 2.64 2.64 0 00-1.871-.774zM123.552 228.194a8.504 8.504 0 01-6.031-2.494c-3.326-3.326-3.326-8.737 0-12.062 3.324-3.326 8.737-3.326 12.062 0 3.326 3.325 3.326 8.737 0 12.062a8.502 8.502 0 01-6.031 2.494zm0-11.171a2.64 2.64 0 00-1.873.774 2.65 2.65 0 000 3.743 2.65 2.65 0 003.745 0 2.65 2.65 0 000-3.743 2.64 2.64 0 00-1.872-.774z"
                            fill="#c4cdd5"
                            opacity={0.545}
                        />
                        <path
                            d="M272.674 188.022a2.931 2.931 0 01-2.08-.862l-3.508-3.508-3.509 3.508a2.941 2.941 0 01-4.159-4.159l5.588-5.588a2.941 2.941 0 014.16 0l5.588 5.588a2.94 2.94 0 01-2.08 5.02z"
                            fill="#dfe3e8"
                        />
                        <path
                            d="M65.226 176.816a2.931 2.931 0 01-2.08-.861l-3.508-3.509-3.51 3.509a2.941 2.941 0 01-4.158-4.16l5.588-5.588a2.941 2.941 0 014.16 0l5.587 5.588a2.94 2.94 0 01-2.08 5.02z"
                            fill="#d5dbe0"
                        />
                    </g>
                    <g transform="translate(78 50)">
                        <use
                            fill="#dfe3e8"
                            stroke="#919eab"
                            strokeOpacity={0.48}
                            strokeWidth={1.5}
                            xlinkHref={`#${getUniqueId("a")}`}
                        />
                        <g fillRule="nonzero">
                            <path
                                d="M166.96 0c2.823 0 5.04 1.76 5.04 4v36H0V4c0-2.24 2.217-4 5.04-4zM26 15c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm20 0c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm20 0c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5z"
                                fill="#919eab"
                                mask={`url(#${getUniqueId("c")})`}
                                opacity={0.32}
                            />
                            <g
                                stroke="#919eab"
                                strokeDasharray="2 2"
                                strokeWidth={1.2}>
                                <path
                                    d="M146 54H96c-2.764 0-5 2.236-5 5v60c0 2.764 2.236 5 5 5h50c2.764 0 5-2.236 5-5V59c0-2.764-2.236-5-5-5z"
                                    fill="#919eab"
                                    fillOpacity={0.48}
                                    mask={`url(#${getUniqueId("c")})`}
                                />
                                <path
                                    d="M26 64h20c2.764 0 5-2.236 5-5s-2.236-5-5-5H26c-2.764 0-5 2.236-5 5s2.236 5 5 5zM76 74H26c-2.764 0-5 2.236-5 5s2.236 5 5 5h50c2.764 0 5-2.236 5-5s-2.236-5-5-5zM76 94H26c-2.764 0-5 2.236-5 5s2.236 5 5 5h50c2.764 0 5-2.236 5-5s-2.236-5-5-5zM76 114H26c-2.764 0-5 2.236-5 5s2.236 5 5 5h50c2.764 0 5-2.236 5-5s-2.236-5-5-5z"
                                    mask={`url(#${getUniqueId("c")})`}
                                    opacity={0.72}
                                />
                            </g>
                        </g>
                    </g>
                </g>
            </svg>
        </Box>
    );
};

export default EmptyContent;
