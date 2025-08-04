import { gql, useMutation, useQuery } from "@apollo/client";
import { ShopContext } from '../context/ShopContext';
import { useEffect, useState, useRef, useContext } from "react";


//get all attributes for product by id 
const PRODUCTS_BY_IDS = gql`
  query ProductsByIds($ids: [String!]!) {
    ProductsByIds(ids: $ids) { 
      id
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
//increase wuantity by "1" in cookies 
const UPDATE_QUANTITY = gql`
  mutation UpdateCartQuantity($productId: String!, $attributes: [AttributeInput!]!) {
    updateCartQuantity(productId: $productId, attributes: $attributes)
  }
`;
//decrese quantity by "1" in cookies
const DECREASE_QUANTITY = gql`
  mutation DecreaseQuantity($productId: String!, $attributes: [AttributeInput!]!) {
    decreaseQuantity(productId: $productId, attributes: $attributes)
  }
`;
//delete cart items in cookies 
const DELETE_QUANTITY = gql`
  mutation DeleteQuantity($productId: String!, $attributes: [AttributeInput!]!) {
    deleteQuantity(productId: $productId, attributes: $attributes)
  }
`;

function SideCart() {
  //get values from shopContext
  const { value4, value5, value6 } = useContext(ShopContext);
  const numberFormat = value5;
  const addToCart = value4;
  const cartData = value6;

  // mutation for increase quantity
  const [updateQuantity] = useMutation(UPDATE_QUANTITY);
  //mutation for decrease quantity
  const [decreaseQuantity] = useMutation(DECREASE_QUANTITY);
  //mutation for delete item in cookies
  const [deleteQuantity] = useMutation(DELETE_QUANTITY);

  const cartIds = cartData.map(item => item.id) || [];

  const { data: productsData, loading: productsLoading } = useQuery(PRODUCTS_BY_IDS, {
    variables: { ids: cartIds },
    skip: cartIds.length === 0,
  });

  let total = 0;
  let totalprice = 0;
  //function to increase quantity
  const handleIncrease = (item) => {
    updateQuantity({
      variables: {
        productId: item.id,
        attributes: item.attributes.map(attr => ({
          attribute_name: attr.attribute_name,
          value: attr.value
        }))
      }
    }).catch((err) => {
      console.error("Error increasing quantity:", err.message);
    });
  };
  //function to decrease quantiy
  const decrease = async (item) => {
    if (item.quantity === 1) {
      deleteQuantity({
        variables: {
          productId: item.id,
          attributes: item.attributes.map(attr => ({
            attribute_name: attr.attribute_name,
            value: attr.value
          }))
        }
      });
    } else {
      decreaseQuantity({
        variables: {
          productId: item.id,
          attributes: item.attributes.map(attr => ({
            attribute_name: attr.attribute_name,
            value: attr.value
          }))
        }
      });
    }
  };
  //function to delete item in cookies
  const handleDelete = async (item) => {
    deleteQuantity({
      variables: {
        productId: item.id,
        attributes: item.attributes.map(attr => ({
          attribute_name: attr.attribute_name,
          value: attr.value
        }))
      }
    });
  };

  return (
    <div className="sidecart-overlay"  >

      <p className="pcart">Your Cart</p>
      <div className="scroll">
        {cartData.map((item) => {
          total = item.price * item.quantity;
          totalprice += total;

          return (
            <div className="content12" key={item.id + JSON.stringify(item.attributes)}>
              <div>
                <img className="cartimg" src={item.image} alt={item.name} />
                <p className="cartcontent">{item.name}</p>
                <div className="cartdata">
                  <div className="small">
                    <p data-testid={`${numberFormat(item.price)}`} className="price">{numberFormat(item.price)}</p>
                    <div className="cartdata1">
                      <img
                        className="cartremove"
                        onClick={() => handleDelete(item)}
                        src={require("../assets/images/close.png")}
                        alt="Remove"
                      />
                      <div className="cartcontent1">
                        <button
                          className="cartquantity1"
                          data-testid='cart-item-amount-decrease'
                          onClick={() => decrease(item)}
                        >-</button>
                        <button className="cartquantity2">{item.quantity}</button>
                        <button
                          className="cartquantity3"
                          data-testid='cart-item-amount-increase'
                          onClick={() => handleIncrease(item)}
                        >+</button>
                      </div>
                    </div>
                  </div>

                  <div className="attribute">
                    {productsData?.ProductsByIds?.filter(product => product.id === item.id).slice(0, 1).map((product) => (
                      <div key={product.id}>
                        {product.attributes.map((attribute) => {
                          const selected = item.attributes.find(a => a.attribute_name === attribute.name)?.value;

                          return (
                            <div
                              className="section"
                              data-testid={`cart-item-attribute-${attribute.name.toLowerCase()}`}
                              key={attribute.name}
                            >
                              <p className="nameattr">{attribute.name}</p>
                              <div className="allattributes">
                                {attribute.items.map((pro) => {
                                  const isSelected = selected === pro.value;

                                  return (
                                    <div key={pro.id}>
                                      {attribute.name === 'Color' ? (
                                        <button
                                          disabled
                                          className={`buttonpro ${isSelected ? "selected" : ""}`}
                                          style={{ backgroundColor: pro.value }}
                                          data-testid={`cart-item-attribute-${attribute.name.toLowerCase()}-${selected}${isSelected ? "-selected" : ""}`}
                                        ></button>
                                      ) : (
                                        <button
                                          disabled
                                          className={isSelected ? "selected" : ""}
                                          data-testid={`cart-item-attribute-${attribute.name.toLowerCase()}-${selected}${isSelected ? "-selected" : ""}`}
                                        >
                                          {pro.value}
                                        </button>
                                      )}
                                    </div>
                                  );
                                })}
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      <div className="cartborder1">
        <div className="totalblock3">
          <p>Total :</p>
          <p data-testid={`${numberFormat(totalprice)}`}>{numberFormat(totalprice)}</p>
        </div>
        <button className="submittbutton" onClick={addToCart}>Place Order</button>
      </div>
    </div>
  )
}

export default SideCart;
