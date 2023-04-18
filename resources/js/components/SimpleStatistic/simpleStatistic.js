import React from "react";
import {Card, Stack, Typography} from "@mui/material";
import ChangeTrend from "components/ChangeTrend";
import {FormattedMessage} from "react-intl";
import PropTypes from "prop-types";
import Spin from "components/Spin";

const SimpleStatistic = ({
    content,
    title,
    loading = false,
    change = null,
    changeText = <FormattedMessage defaultMessage="from last period" />
}) => {
    return (
        <Card>
            <Spin spinning={loading}>
                <Stack sx={{p: 3}}>
                    <Typography variant="subtitle2" noWrap>
                        {title}
                    </Typography>

                    <Typography variant="h3" sx={{whiteSpace: "nowrap"}}>
                        {content ?? <FormattedMessage defaultMessage="----" />}
                    </Typography>

                    <ChangeTrend description={changeText} change={change} />
                </Stack>
            </Spin>
        </Card>
    );
};

SimpleStatistic.propTypes = {
    title: PropTypes.any.isRequired
};

export default SimpleStatistic;
