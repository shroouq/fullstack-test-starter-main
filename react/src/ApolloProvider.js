import React from 'react';
import {ApolloClient,InMemoryCache,ApolloProvider, HttpLink} from '@apollo/client';

const client = new ApolloClient({
  // uri: 'http://localhost/fullstack-test-starter-main/public/graphql', 
  uri:'https://api.mtegypt.online/public/graphql',

  cache: new InMemoryCache({
    typePolicies: {
      CartItem: {
        keyFields: ["id", "attributes"],
      },
    },
  }),credentials: 'include',
});

const Provider = ({ children }) => (
  <ApolloProvider client={client}>{children}</ApolloProvider>
);

export default Provider;
