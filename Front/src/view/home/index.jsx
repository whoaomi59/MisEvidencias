import { useEffect, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import { URL } from "../../App";

export default function MesesPanel() {
  const [meses, setMeses] = useState([]);
  const [nuevoMes, setNuevoMes] = useState({
    nombre: "",
    fecha_inicio: "",
    fecha_fin: "",
  });
  const navigate = useNavigate();

  // Cargar meses al iniciar
  useEffect(() => {
    axios.get("/meses.php").then((res) => {
      setMeses(res.data);
    });
  }, []);

  const crearMes = async () => {
    if (!nuevoMes.nombre || !nuevoMes.fecha_inicio || !nuevoMes.fecha_fin)
      return;

    await axios.post("/meses.php", nuevoMes);
    const res = await axios.get("/meses.php");
    setMeses(res.data);
    setNuevoMes({ nombre: "", fecha_inicio: "", fecha_fin: "" });
  };

  const descargarMes = (mesId) => {
    window.open(`${URL}/descargar_mes.php?id=${mesId}`, "_blank");
  };

  return (
    <div className="max-w-5xl mx-auto p-4 sm:p-6 space-y-10">
      <h1 className="text-xl sm:text-2xl font-bold">Mis Evidencias</h1>

      {/* Crear nuevo mes */}
      <div className="bg-white shadow-md rounded-xl p-4 space-y-4">
        <h2 className="text-lg font-semibold">Crear una Actividad</h2>

        <input
          type="text"
          placeholder="Nombre del la Actividad (Ej. GC_1004419254_22225_MAY_2025)"
          className="border rounded px-3 py-2 w-full text-sm"
          value={nuevoMes.nombre}
          onChange={(e) => setNuevoMes({ ...nuevoMes, nombre: e.target.value })}
        />

        {/* Fechas responsive */}
        <div className="flex flex-col sm:flex-row gap-2">
          <input
            type="date"
            className="border rounded px-3 py-2 w-full text-sm"
            value={nuevoMes.fecha_inicio}
            onChange={(e) =>
              setNuevoMes({ ...nuevoMes, fecha_inicio: e.target.value })
            }
          />
          <input
            type="date"
            className="border rounded px-3 py-2 w-full text-sm"
            value={nuevoMes.fecha_fin}
            onChange={(e) =>
              setNuevoMes({ ...nuevoMes, fecha_fin: e.target.value })
            }
          />
        </div>

        <button
          onClick={crearMes}
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm w-full sm:w-auto"
        >
          Crear Actividad 🗃️​
        </button>
      </div>

      {/* Lista de meses */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {meses.map((mes) => (
          <div
            key={mes.id}
            className="bg-white shadow rounded-lg p-4 flex flex-col justify-between"
          >
            <div className="space-y-1">
              <h3 className="text-lg sm:text-xl font-semibold text-blue-800">
                {mes.nombre}
              </h3>
              <p className="text-sm text-gray-500">
                {new Date(mes.fecha_inicio).toLocaleDateString()} -{" "}
                {new Date(mes.fecha_fin).toLocaleDateString()}
              </p>
            </div>
            <div className="flex flex-col mt-4 gap-2">
              <button
                onClick={() => navigate(`/mes/${mes.id}`)}
                className="bg-green-600 text-white px-3 py-1 text-sm rounded hover:bg-green-700"
              >
                Ver Carpetas
              </button>
              <button
                onClick={() => descargarMes(mes.id)}
                className="bg-gray-600 text-white px-3 py-1 text-sm rounded hover:bg-gray-800"
              >
                Descargar PDF
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
