import { useState, useEffect, createContext } from "react";
import $ from "jquery";
import { gql, useMutation, useQuery } from "@apollo/client";

//get cart from cookies
const GET_CART = gql`
  query {
    getCartFromCookie {
      id
      name
      category
      price
      image
      quantity
        attributes {
        attribute_name
        value
      }
    }
  }
`;

export const ShopContext = createContext();
const ShopContextProvider = ({ children }) => {
  const [cartData, setCartData] = useState([]);

  const [cartVisible, setCartVisible] = useState(false);

  const { data } = useQuery(GET_CART, {
    fetchPolicy: "network-only",
    credentials: "include",
    pollInterval: 1000,
  });

  useEffect(() => {
    if (data?.getCartFromCookie) {
      setCartData(data.getCartFromCookie);
    }
  }, [data]);

  //  mouseposition 
  const [mousePosition, setMousePosition] = useState({
    x: 0,
    y: 0
  });
  useEffect(() => {
    const mouseMove = e => {
      setMousePosition({
        x: e.clientX,
        y: e.clientY
      })

    }
    window.addEventListener("mousemove", mouseMove)
    return () => {
      window.removeEventListener("mousemove", mouseMove)
    }
  }, []);


  //variants to get position to move div with mouse
  const variants = {
    default: {
      x: mousePosition.x + $(document).scrollLeft() - 16,
      y: mousePosition.y + $(document).scrollTop() - 16
    }
  }
  //sidecart open function
  const sidecart = () => {

    setCartVisible(true);

  }

  //sidecart close function
  const closecart = () => {


    setCartVisible(false)
  }

  //save cart in cookies from database for default items 
  const SAVE_CART_FROM_COOKIE = gql`
  mutation {
    saveCartToDatabase
  }
`;

  const [saveCartFromCookie] = useMutation(SAVE_CART_FROM_COOKIE);

  const addToCart = async () => {
    console.log("Saving cart...");
    try {
      const res = await saveCartFromCookie();
      console.log("Cart saved?", res.data.saveCartFromCookie);
    } catch (error) {
      console.error("Error saving cart:", error);
    }
  };

  const numberFormat = (value) =>
    new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);


  return (

    <ShopContext.Provider value={{ value1: sidecart, value2: cartVisible, value3: closecart, value4: addToCart, value5: numberFormat, value6: cartData }}>
      {children}
    </ShopContext.Provider>
  )
}

export default ShopContextProvider;
