import React from 'react';
import { isString } from 'lodash';
import { Box } from '@mui/material';

const IconBuilder = ({ icon: svgIcon, sx: iconStyle, ...otherProps }) => {
  if (!isString(svgIcon)) return null;

  return (
    <Box
      component="span"
      {...otherProps}
      sx={{
        display: 'flex',
        alignItems: 'center',
        flexShrink: 0,
        ...iconStyle
      }}>
      <Box component="img" sx={{ width: '0.8em', height: '0.8em' }} src={svgIcon} />
    </Box>
  );
};

export default IconBuilder;
