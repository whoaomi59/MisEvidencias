import { useEffect, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";

export default function Cargue() {
  const [meses, setMeses] = useState([]);
  const navigate = useNavigate();

  useEffect(() => {
    // Carga los meses y sus carpetas
    axios.get("/meses_con_carpetas.php").then((res) => {
      setMeses(res.data);
    });
  }, []);

  const crearCarpeta = async (mesId) => {
    const nombre = prompt("Nombre de la nueva carpeta:");
    if (!nombre) return;

    await axios.post("/carpeta.php", {
      mes_id: mesId,
      nombre: nombre,
    });

    // Recargar meses
    const res = await axios.get("/meses_con_carpetas.php");
    setMeses(res.data);
  };

  const descargarMes = (mesId) => {
    window.open(`/descargar_mes.php?id=${mesId}`, "_blank");
  };

  return (
    <div className="max-w-5xl mx-auto p-6 space-y-10">
      <h1 className="text-3xl font-bold mb-6 text-gray-800">
        Gestión de Evidencias
      </h1>

      {meses.map((mes) => (
        <div key={mes.id} className="bg-white shadow rounded-xl p-5 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-2xl font-semibold text-blue-800">
              {mes.nombre}
            </h2>
            <div className="flex gap-2">
              <button
                onClick={() => crearCarpeta(mes.id)}
                className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                Nueva Carpeta 📁
              </button>
            </div>
          </div>

          <ul className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            {mes.carpetas.map((carpeta) => (
              <li key={carpeta.id}>
                <a
                  href={`/Evidencia/${carpeta.id}/${
                    mes.nombre + "," + carpeta.nombre
                  }`}
                  className="flex items-center justify-between p-4 bg-gray-100 rounded hover:bg-gray-200 transition"
                >
                  <span className="text-gray-800 font-medium">
                    {carpeta.nombre}
                  </span>
                  <span className="text-xl">🗂️</span>
                </a>
              </li>
            ))}
          </ul>
        </div>
      ))}
    </div>
  );
}
