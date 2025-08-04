import { gql, useQuery } from '@apollo/client';
import { Navigate } from 'react-router-dom';

const CATEGORY = gql`
  query {
    getcategory {
      name
    }
  }
`;

function FirstCategory() {
  const { data, loading, error } = useQuery(CATEGORY);

  if (loading) return null;
  if (error || !data?.getcategory?.length) return <p>Error loading categories</p>;

  const firstCategory = data.getcategory[0].name.toLowerCase();

  return <Navigate to={`/${firstCategory}`} replace />;
}

export default FirstCategory;
