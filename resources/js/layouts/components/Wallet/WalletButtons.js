import {useAccount, useConnect} from 'wagmi'
import WalletButton from './WalletButton'
import { Stack } from "@mui/material";
import CoinBaseIcon from "static/icons/coinbase.png";
import TrustIcon from "static/icons/trust.svg";
import MetamaskIcon from "static/icons/metamask.png";

const WalletButtons = () => {
  const {isConnected} = useAccount()
  const {connect, connectors} = useConnect()

  return (
    <Stack direction="column">
      <Stack direction ="row" spacing={3}>
          <WalletButton
            title='Coinbase Wallet'
            imgUrl={CoinBaseIcon}
            
            connect={() => !isConnected && connect({connector: connectors[0]})}
          />
      
        <WalletButton
          title='Trust Wallet'
          imgUrl={TrustIcon}
          connect={() => !isConnected && connect({connector: connectors[1]})}
        />
           
      </Stack>
      <Stack direction ="row" sx={{my: 3}}>
          <WalletButton
            title='Metamask Wallet'
            imgUrl={MetamaskIcon}
            connect={() => !isConnected && connect({connector: connectors[2]})}
          />
       </Stack>
       
    </Stack>
  )
}

export default WalletButtons
