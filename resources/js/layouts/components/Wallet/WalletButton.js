import React from "react";
import {
    Button,
    Stack
} from "@mui/material";

const WalletButton = ({
  title,
  imgUrl,
  connect,
} ) => {
  return (
     <Button
        color="inherit"
        variant="outlined"
        size="medium"
        sx={{display: 'flex',  justifyContent: "center", alignItems: "center", width: '100%', borderRadius: '5px'}}
        onClick={connect}  
      >
        <Stack justifyContent="center" alignItems='center' sx={{height: '40px', mr: 2}}>
            <img alt={title} src={imgUrl} width={40} height={40}  />
        </Stack>
      {title}
      </Button>
     
  )
}
export default WalletButton
