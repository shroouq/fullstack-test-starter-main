import { useState, useEffect, createContext } from "react";
import { useQuery, useMutation, gql } from '@apollo/client';
import { useParams } from "react-router-dom";
import img1 from "./assets/images/arrow.png";
import { ShopContext } from './context/ShopContext';
import { useContext } from "react";



//save cart in cookies
const ADD_TO_CART = gql`
  mutation SaveCookies($input: ProductInput!) {
    saveCookies(input: $input)
  }
`;

//fetch product by id 
const FETCH_PRODUCT = gql`
  query GetProduct($id: String!) {
    product(id: $id) {
      id
      name
      inStock
      category
      description
      brand
      price {
        amount
        currencysymbol
      }
      gallery {
        pic
      }
      attributes {
        name
        items {
          id
          value
        }
      }
    }
  }
`;


function Product() {
  const { value1, value2, value5 } = useContext(ShopContext);
  const cartVisible = value2;
  const sidecart2 = value1;

  const numberFormat = value5;
  const { id } = useParams();
  const { data, error, loading } = useQuery(FETCH_PRODUCT, { variables: { id } });
  const product = data?.product;
  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedAttributes, setSelectedAttributes] = useState({});
  const [saveCookies] = useMutation(ADD_TO_CART);

  //next slide when click on arrow button
  const nextSlide = () => {
    setCurrentIndex((prevIndex) =>
      prevIndex === product.gallery.length - 1 ? 0 : prevIndex + 1
    );
  };
  //previous slide when click on arrow button 
  const prevSlide = () => {
    setCurrentIndex((prevIndex) =>
      prevIndex === 0 ? product.gallery.length - 1 : prevIndex - 1
    );
  };


  const handleButton = (attribute, value) => {
    setSelectedAttributes((prev) => ({
      ...prev,
      [attribute]: value
    }));
  };

  //addto cart function 
  const addToCart = () => {
    const attributesArray = Object.entries(selectedAttributes).map(
      ([attribute_name, value]) => ({ attribute_name, value })


    );

    const input = {
      id: product.id,
      name: product.name,
      category: product.category,
      amount: parseFloat(product.price.amount),
      pic: product.gallery[0]?.pic,
      attributes: attributesArray,
    };
    const products = data?.product;

    saveCookies({ variables: { input } })
    sidecart2();
  };

  if (loading) return null;
  if (error) return <p>Error: {error.message}</p>;

  const isAddToCartDisabled =
    Object.keys(selectedAttributes).length !== product.attributes.length;

  return (
    <div id="u" className={`${cartVisible ? "parent1" : 'parent'}`}>
      <div className='productpage'>
        <div className='productimage'>
          <div className='productimage2'>
            <div className='productimage3'>
              {product.gallery.map((img, index) => (
                <img
                  data-testid="product-gallery"
                  onClick={() => setCurrentIndex(index)}
                  key={index}
                  src={img.pic}
                  className='productimage4'
                  alt=""
                />
              ))}
            </div>

            <div className="container">
              <div className="slide">
                <img
                  src={product.gallery[currentIndex].pic}
                  className="slide-image"
                />
              </div>
              <img src={img1} onClick={prevSlide} className="arrow left" />
              <img src={img1} onClick={nextSlide} className="arrow right" />
            </div>

            <div className='productdetails'>
              <div className='bb'>
                <h4>{product.name}</h4>
              </div>
              <div className='bb'>

                {product.inStock == "false" && <h4> Out Of Stock</h4>}
              </div>
              <div className='bb'>
                <h3> Price</h3>
                <p className='priceamount'>  {numberFormat(product.price.amount)} </p>
              </div>


              {product.attributes.map((attribute) => {
                return (
                  <div className='bb' key={attribute.name} data-testid={`product-attribute-${attribute.name.toLowerCase()}`}>
                    <h4>{attribute.name}</h4>
                    <div
                      className="attribute-items"
                    >
                      {attribute.items.map((item) => {
                        const isSelected = item.value === selectedAttributes[attribute.name];
                        return (
                          <div key={item.id}>
                            {attribute.name === 'Color' ? (
                              <button
                                style={{ backgroundColor: item.value }}
                                className={isSelected ? "colorbutton2" : "colorbutton"}
                                onClick={() => handleButton(attribute.name, item.value)} data-testid={
                                  selectedAttributes[attribute.name] === item.value
                                    ? `product-attribute-${attribute.name.toLowerCase()}-${item.value}`
                                    : undefined} />) : (
                              <button
                                className={isSelected ? "attribute-item2" : "attribute-item"}
                                onClick={() => handleButton(attribute.name, item.value)}
                                data-testid={
                                  selectedAttributes[attribute.name] === item.value
                                    ? `product-attribute-${attribute.name.toLowerCase()}-${item.value}`
                                    : undefined}  >
                                {item.value}
                              </button>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                );
              })}

              {isAddToCartDisabled || product.inStock == "false" ? (
                <button disabled data-testid="add-to-cart" className='addtocartbutton2'>Add To Cart</button>
              ) : (
                <button
                  data-testid="add-to-cart"
                  className='addtocartbutton'
                  onClick={addToCart}
                >
                  Add To Cart
                </button>
              )}

              <div className='bb'>
                <h4>Description</h4>
              </div>
              <div
                data-testid="product-description"
                className='bb'
              >
                {product.description}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Product;
