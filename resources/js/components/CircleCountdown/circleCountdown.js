import React from "react";
import {CountdownCircleTimer} from "react-countdown-circle-timer";
import {parseDate} from "utils/helpers";
import {useTheme} from "@mui/material/styles";
import {now} from "utils/dayjs";

const CircleCountdown = ({
    date,
    duration,
    renderer,
    isPlaying = true,
    size = 100,
    ...otherProps
}) => {
    const theme = useTheme();
    const difference = parseDate(date).diff(now(), "second");

    const interval = Math.floor(duration / 4);

    return (
        <CountdownCircleTimer
            {...otherProps}
            size={size}
            isPlaying={isPlaying}
            duration={duration}
            initialRemainingTime={difference}
            trailColor={theme.palette.background.neutral}
            colorsTime={[3, 2, 1, 0].map((i) => interval * i)}
            colors={[
                theme.palette.success.dark,
                theme.palette.info.dark,
                theme.palette.warning.dark,
                theme.palette.error.dark
            ]}>
            {({remainingTime, color}) => {
                const hours = Math.floor(remainingTime / 3600);
                const minutes = Math.floor((remainingTime % 3600) / 60);
                const seconds = Math.floor(remainingTime % 60);

                return typeof renderer === "function"
                    ? renderer({hours, minutes, seconds, color})
                    : `${hours}:${minutes}:${seconds}`;
            }}
        </CountdownCircleTimer>
    );
};

export default CircleCountdown;
