import { gql, useQuery } from "@apollo/client";
import { useEffect, useState, } from "react";
import { Link, useLocation } from 'react-router-dom';
import { motion } from "framer-motion";
import $ from "jquery";
import { useContext } from "react";
import { ShopContext } from './context/ShopContext';
import "./Apps.css";
import { ShoppingCart } from 'phosphor-react';
import SideCart from './component/SideCart';

//get category from database
const CATEGORY = gql`
  query {
    getcategory {
      name
    }
  }
`;



function Header() {
  const { value1, value2, value3, value6 } = useContext(ShopContext);
  const sidecart2 = value1;
  const cartVisible = value2;
  const closecart2 = value3;
  const cartData = value6;
  const location = useLocation();


  const { data, error, loading } = useQuery(CATEGORY, {
    fetchPolicy: "network-only",
  });


  let totalquantity = 0;
  if (cartData?.length > 0) {
    cartData.forEach(item => {
      totalquantity += item.quantity;
    });
  }
  //open cart
  const sidecart = () => {
    document.body.style.cursor = "none";
    document.getElementById("w").style.cursor = "default";
    if (cartVisible) {
      document.body.style.cursor = "default";
      closecart2()
    } else {
      sidecart2();
    }

  }
  //get mouse position
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
  //function to close cart
  const closecart = () => {
    document.getElementById("jj").addEventListener('click', function (e) {
      if (cartVisible) {
        document.body.style.cursor = "default";
        closecart2()
      }
    })
  }


  if (loading) return null;
  if (error) return <p>Error: {error.message}</p>;


  return (

    <div >

      <div className="nn" id="jj" onClick={() => closecart()}>
        <div className="mousediv">
          <motion.div className={`${cartVisible ? "circlecart" : 'circlecart1'}`} variants={variants} animate="default">
            <div className="cross1"></div>
            <div className="cross2"></div>

          </motion.div>
        </div>
      </div>
      <div className="header">
        <nav className="nav">

          {data.getcategory.map((item) => {
            const isActive = location.pathname === `/${item.name}`;
            return (
              <Link
                key={item.name}
                to={`/${item.name}`}
                className={({ isActive }) => (isActive ? "b active" : "b")}
                data-testid={isActive ? "active-category-link" : "category-link"}
              >
                {item.name}
              </Link>
            );
          })}

          <div>
            <div>

              <div>

                <button
                  data-testid="cart-btn"
                  className={totalquantity !== 0 ? 'cart-button' : 'cart-button2'}
                  onClick={sidecart}
                >
                  <ShoppingCart size={32} />
                </button>
                {/* {totalquantity == "0" ? ( <button disabled className="cart-button2"> <ShoppingCart size={32}/> </button> ):(  */}
                {/* <button className="cart-button"  data-testid='cart-btn' onClick={()=> sidecart()} > < ShoppingCart size={32} /> </button> */}

                {/* <button
  className={totalquantity == "0" ? "cart-button2" : "cart-button"}
  data-testid="cart-btn"
  disabled={totalquantity == "0"}
  onClick={() => {
     sidecart();
  }}
> <ShoppingCart size={32} /> </button> */}


                {totalquantity != "0" && <p className="circle2" >   {totalquantity} item </p>}
              </div>
            </div>
          </div>
        </nav>
      </div>
      <div
        id="w" data-testid="cart-overlay" className={` ${cartVisible ? 'visible' : 'closeside'}`}>
        <SideCart />
      </div>
    </div>
  )


};

export default Header;