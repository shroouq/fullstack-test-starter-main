import { Route, Routes } from 'react-router-dom';
import Product from "./Product";
import FirstCategory from "./component/FirstCategory"
import Category from "./Category";

function Router() {


    return (
        <div>
            <Routes>
                {/* <Route path="/" element={<Navigate to="/all" replace />} /> */}
                <Route path="/" element={<FirstCategory />} />
                <Route path="/:category" element={<Category />} />
                <Route path="/product/:id" element={<Product />} />

            </Routes>
        </div>
    )


};
export default Router;