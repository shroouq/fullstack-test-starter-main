import { useQuery, useMutation, gql } from '@apollo/client';
import { useParams } from 'react-router-dom';
import { Link } from "react-router-dom";
import { useContext, } from "react";
import { ShopContext } from './context/ShopContext';
import { ShoppingCart } from 'phosphor-react';




const SAVE_PRODUCT = gql`
  mutation SaveProduct($productId: String!) {
    saveProduct(productId: $productId)
  }
`;

//get products from database 
const GET_PRODUCTS = gql`
  query {
    products{
      id
      name
      inStock
      image
      price {
      amount
      currencysymbol
    }
    }
  }
`;


//get products from database by category
const PRODUCTS_BY_CATEGORY = gql`
  query ($category: String!) {
    productsByCategory(category: $category) {
      id
      name
      image
      inStock
      price {
      amount
      currencysymbol
    }
    }
  }
`;


function Category() {
  //values from shopContext
  const { value1, value2, value5 } = useContext(ShopContext);
  const numberFormat = value5;
  const sidecart2 = value1;
  const cartVisible = value2;
  const { category } = useParams();



  const { data, loading, error } = useQuery(
    category === "all" ? GET_PRODUCTS : PRODUCTS_BY_CATEGORY,
    {
      variables: { category },
      fetchPolicy: "network-only",
    }
  );

  const [saveProduct] = useMutation(SAVE_PRODUCT);


  if (loading) return null;
  if (error) return <p>Error: {error.message}</p>;

  //add product to cookies 
  const handleAddToCart = (productId) => {
    saveProduct({ variables: { productId } })
      .catch(err => console.error("Error:", err));
    sidecart2();

  };
  const products =
    category === "all" ? data.products : data.productsByCategory;

  return (

    <div id="u" className={`${cartVisible ? "parent1" : 'parent'}`}>


      <div className='product'>



        {products.map((product, i) => (


          <div key={i}>

            <div className='card' data-testid={`product-${product.name.toLowerCase().replace(/\s+/g, '-')}`}
            >


              <Link to={`/product/${product.id}`}>
                <img className='cardimage' src={product.image} />
              </Link>

              <div className='soldoutdiv'>
                {product.inStock == "false" && <button className="soldout" >sold out </button>}

              </div>
              <div className='n'>

                {product.inStock == "true" &&
                  <button className="cartadd" onClick={() => handleAddToCart(product.id)}
                  >
                    <ShoppingCart className='shoppingcart' size={32} />
                  </button>}
              </div>

              <div className='container1'>

                <h3> {product.name}</h3>

                <p>  {numberFormat(product.price.amount)} </p>



              </div>

            </div>
          </div>

        ))}





      </div>
    </div>
  )

}
export default Category;
