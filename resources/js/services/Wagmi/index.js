import { publicProvider } from 'wagmi/providers/public'
import { MetaMaskConnector } from 'wagmi/connectors/metaMask'
import { createClient, configureChains } from 'wagmi'
import { infuraProvider } from 'wagmi/providers/infura'
import {CoinbaseWalletConnector} from 'wagmi/connectors/coinbaseWallet'
import {WalletConnectConnector} from 'wagmi/connectors/walletConnect'
import { arbitrumGoerli } from '@wagmi/chains'

// Configure chains & providers with the Alchemy provider.
// Two popular providers are Alchemy (alchemy.com) and Infura (infura.io)
const { chains, provider, webSocketProvider } = configureChains(
	[arbitrumGoerli],
	[infuraProvider({ apiKey: 'ed7ccfee4b8147daa0b4d5417080d36d' }), publicProvider()]
)

// Set up client
const client = createClient({
  autoConnect: true,
  connectors: [
    new CoinbaseWalletConnector({
      chains,
      options: {
        appName: 'wagmi',
      },
    }),
    new WalletConnectConnector({
      chains,
      options: {
        projectId: 'b17ff146b85bed60d4d3ae7674275311',
      },
    }),
    new MetaMaskConnector({chains}),
  ],
  provider,
  webSocketProvider,
})


export default client
