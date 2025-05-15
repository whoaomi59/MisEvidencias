import { URL } from "../App";

export default function EvidenciaCard({ evidencia }) {
  return (
    <div className="bg-white shadow rounded p-4 space-y-2">
      <h2 className="text-xl font-semibold text-blue-800">
        {evidencia.nombre}
      </h2>
      <p className="text-sm text-gray-600">
        Fecha: {new Date(evidencia.fecha).toLocaleString()}
      </p>
      <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
        {evidencia.imagenes.map((imgObj, index) => (
          <img
            key={index}
            src={`${URL}/${imgObj.img}`}
            alt={`evidencia-${index}`}
            className="w-full h-40 object-cover rounded"
          />
        ))}
      </div>
    </div>
  );
}
