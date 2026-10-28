import test from "node:test";
import assert from "node:assert/strict";
import {
  filterApplications,
  metrics,
  toCsv,
  isApplication,
} from "../src/domain/applications";
import { demoApplications } from "../src/domain/demo";

test("combina busca sem acentos, etapa e modalidade", () => {
  assert.deepEqual(
    filterApplications(demoApplications, "nuvem junior", "applied", true).map(
      (i) => i.id,
    ),
    [2],
  );
  assert.equal(
    filterApplications(demoApplications, "Forma", "all", true).length,
    0,
  );
});
test("calcula etapas ativas sem contar vagas salvas ou processos encerrados", () => {
  assert.deepEqual(metrics(demoApplications), {
    total: 7,
    active: 4,
    interviews: 1,
    offers: 1,
    responseRate: 40,
  });
  assert.equal(metrics([]).responseRate, 0);
});
test("CSV preserva separadores, aspas e bloqueia fórmulas fornecidas pelo usuário", () => {
  const csv = toCsv([
    { ...demoApplications[0], company: "=SUM(A1:A9)", role: 'Dev; "Vue"' },
  ]);
  assert.ok(csv.includes('"\'=SUM(A1:A9)"'));
  assert.ok(csv.includes('"Dev; ""Vue"""'));
});
test("recuperação local rejeita registros malformados", () => {
  assert.equal(isApplication(demoApplications[0]), true);
  assert.equal(
    isApplication({ ...demoApplications[0], status: "unknown" }),
    false,
  );
  assert.equal(
    isApplication({ ...demoApplications[0], salary_min: -1 }),
    false,
  );
  assert.equal(
    isApplication({ ...demoApplications[0], remote: "true" }),
    false,
  );
});
