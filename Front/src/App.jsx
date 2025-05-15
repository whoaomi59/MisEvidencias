import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import "./App.css";
import Cargue from "./view/cargue";
import EvidenciaDetalle from "./view/evidencias/EvidenciaDetalle";
import MesesPanel from "./view/home";
import axios from "axios";

/* export const URL = "http://localhost/MisEvindencias/API/"; */
export const URL = "https://asuprocolombiasas.com/php/MisEvidencias/";

function App() {
  axios.defaults.baseURL = URL;
  return (
    <Router>
      <Routes>
        <Route path="/" element={<MesesPanel />} />
        <Route path="/mes/:mesId" element={<Cargue />} />
        <Route path="/Evidencia/:id/:name" element={<EvidenciaDetalle />} />
        <Route path="*" element={<h1>Not Fount</h1>} />
      </Routes>
    </Router>
  );
}

export default App;
