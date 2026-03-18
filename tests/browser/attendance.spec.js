const { test, expect, devices } = require("@playwright/test");

// const URL = "http://localhost:8000";
const URL = "https://mvgiaoxu.org";
const EMAIL = "son@gmail.com";
const PASSWORD = "0338300666";
const CLASS_ID = 45;

const iPhone = devices["iPhone 13"];

test.setTimeout(600000);

async function login(page) {
  await page.goto(`${URL}/login`, { timeout: 60000 });
  await page.fill('input[name="email"]', EMAIL);
  await page.fill('input[name="password"]', PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForURL(`${URL}/**`);
}

async function waitForLivewireIdle(page) {
  // Chờ Livewire load xong
  await page
    .waitForFunction(
      () => {
        return (
          typeof window.livewire !== "undefined" ||
          typeof window.Livewire !== "undefined"
        );
      },
      { timeout: 15000 },
    )
    .catch(() => {
      console.log("[warn] Livewire object không tìm thấy");
    });

  // Chờ Alpine x-data load xong — kiểm tra hasDraft có tồn tại không
  await page
    .waitForFunction(
      () => {
        const el = document.querySelector("[x-data]");
        if (!el) return false;
        try {
          // Kiểm tra Alpine đã khởi tạo component chưa
          return el._x_dataStack !== undefined;
        } catch (e) {
          return false;
        }
      },
      { timeout: 15000 },
    )
    .catch(() => {
      console.log("[warn] Alpine chưa khởi tạo xong");
    });

  await page.waitForTimeout(800);
}

// ==================== DESKTOP ====================

test("desktop - mo trang", async ({ page }) => {
  await login(page);
  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  await waitForLivewireIdle(page);

  await expect(page.locator("body")).not.toContainText("Whoops");
  await expect(page.locator("body")).not.toContainText("500");

  // Desktop phải thấy table
  await expect(page.locator("table").first()).toBeVisible();
});

test("desktop - luu diem danh", async ({ page }) => {
  await login(page);
  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  await waitForLivewireIdle(page);

  await page.waitForSelector("table");
  await page.locator('button[aria-label="Có mặt"]').first().click();
  await page.locator('button:has-text("Lưu điểm danh")').first().click();

  await expect(page.locator("text=Đã lưu")).toBeVisible({ timeout: 5000 });
});

// ==================== MOBILE ====================

test("mobile - mo trang", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  page.on("console", (msg) =>
    console.log(`[browser ${msg.type()}] ${msg.text()}`),
  );
  page.on("pageerror", (err) =>
    console.error(`[browser error] ${err.message}`),
  );

  await login(page);
  console.log("[test] Đăng nhập xong");

  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
  console.log("[test] Đã vào trang");

  // Log viewport
  const innerWidth = await page.evaluate(() => window.innerWidth);
  console.log(`[test] window.innerWidth = ${innerWidth}`);

  // Chờ Livewire sẵn sàng
  await page
    .waitForFunction(
      () => {
        return (
          typeof window.livewire !== "undefined" ||
          typeof window.Livewire !== "undefined"
        );
      },
      { timeout: 15000 },
    )
    .catch(() => {});

  // Emit lại viewModeDetected để Livewire biết đang ở mobile
  await page.evaluate(() => {
    const isMobile = window.innerWidth < 1024;
    console.log("[inject] emitting viewModeDetected, isMobile =", isMobile);

    // Livewire 2 dùng Livewire.emit
    if (window.Livewire) {
      window.Livewire.emit("viewModeDetected", isMobile ? "mobile" : "desktop");
    } else if (window.livewire) {
      window.livewire.emit("viewModeDetected", isMobile ? "mobile" : "desktop");
    }
  });
  console.log("[test] Đã emit viewModeDetected");

  // Chờ Livewire xử lý xong emit — re-render component
  await page.waitForTimeout(2000);
  await waitForLivewireIdle(page);

  // Log mobileSessionId sau khi emit
  const mobileSessionId = await page.evaluate(() => {
    // Tìm trong DOM xem session đã load chưa
    const dateButtons = document.querySelectorAll(
      'button[wire\\:click*="selectDate"]',
    );
    return `${dateButtons.length} date buttons found`;
  });
  console.log(`[test] ${mobileSessionId}`);

  // Assertions
  await expect(page.locator("body")).not.toContainText("Whoops");
  await expect(page.locator("body")).not.toContainText("500");

  await expect(
    page.locator('button[wire\\:click*="selectDate"]').first(),
  ).toBeVisible({ timeout: 15000 });
  console.log("[test] Date selector visible — mobile UI đúng");

  // Screenshot
  await page.screenshot({
    path: "tests/browser/screenshots/mobile-mo-trang.png",
    fullPage: true,
  });
  console.log("[test] Screenshot lưu xong");

  await ctx.close();
});

test("mobile - bam diem danh va luu", async ({ browser }) => {
  const TOTAL_USERS = 10;

  // --- Helper: chạy 1 phiên điểm danh ---
  async function runOneSession(userId) {
    const start = Date.now();
    const ctx = await browser.newContext({
      ...iPhone,
      viewport: { width: 390, height: 844 },
    });
    const page = await ctx.newPage();

    page.on("console", (msg) =>
      console.log(`[user${userId}][${msg.type()}] ${msg.text()}`),
    );
    page.on("pageerror", (err) =>
      console.error(`[user${userId}][pageerror] ${err.message}`),
    );

    const timings = {};

    try {
      // 1. Login
      let t = Date.now();
      await login(page);
      timings.login = Date.now() - t;
      console.log(`[user${userId}] Đăng nhập xong (${timings.login}ms)`);

      // 2. Vào trang
      t = Date.now();
      await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
      timings.pageLoad = Date.now() - t;
      console.log(`[user${userId}] Đã vào trang (${timings.pageLoad}ms)`);

      // 3. Emit mobile mode
      await page
        .waitForFunction(
          () =>
            typeof window.Livewire !== "undefined" ||
            typeof window.livewire !== "undefined",
          { timeout: 15000 },
        )
        .catch(() =>
          console.log(`[user${userId}][warn] Livewire không tìm thấy`),
        );

      await page.evaluate(() => {
        const emit = window.Livewire || window.livewire;
        if (emit) emit.emit("viewModeDetected", "mobile");
      });

      await page.waitForTimeout(2000);
      await waitForLivewireIdle(page);

      // 4. Chờ danh sách học sinh
      await page.waitForSelector('.lg\\:hidden button[aria-label="Có mặt"]', {
        timeout: 15000,
      });

      const studentCount = await page
        .locator('.lg\\:hidden button[aria-label="Có mặt"]')
        .count();
      console.log(`[user${userId}] Số học sinh = ${studentCount}`);

      // 5. Tap có mặt học sinh 1
      await page
        .locator('.lg\\:hidden button[aria-label="Có mặt"]')
        .first()
        .tap();

      await page.waitForTimeout(300);
      const isGreen = await page.evaluate(() => {
        const btn = document.querySelector(
          '.lg\\:hidden button[aria-label="Có mặt"]',
        );
        return btn?.classList.contains("bg-green-500");
      });
      console.log(`[user${userId}] Nút có mặt xanh = ${isGreen}`);

      // 6. Tap vắng không phép học sinh 2
      await page
        .locator('.lg\\:hidden button[aria-label="Vắng không phép"]')
        .nth(1)
        .tap();

      await page.waitForTimeout(300);
      const draftCount = await page
        .locator('button:has-text("Lưu điểm danh") span')
        .last()
        .textContent()
        .catch(() => "không thấy badge");
      console.log(`[user${userId}] Draft count = ${draftCount}`);

      // 7. Bấm lưu + đo thời gian lưu
      t = Date.now();
      await page.locator('button:has-text("Lưu điểm danh")').last().tap();

      await expect(page.locator("text=Đã lưu")).toBeVisible({ timeout: 8000 });
      timings.save = Date.now() - t;
      console.log(`[user${userId}] Toast Đã lưu (${timings.save}ms)`);

      // 8. Kiểm tra draft bị xóa
      await page.waitForTimeout(500);
      const draftAfter = await page
        .locator('button:has-text("Lưu điểm danh") span')
        .last()
        .textContent()
        .catch(() => "0");
      console.log(`[user${userId}] Draft sau lưu = ${draftAfter}`);

      // 9. Screenshot
      await page.screenshot({
        path: `tests/browser/screenshots/mobile-user${userId}-sau-khi-luu.png`,
        fullPage: true,
      });

      timings.total = Date.now() - start;
      return { userId, status: "ok", timings };
    } catch (err) {
      timings.total = Date.now() - start;
      console.error(`[user${userId}] LỖI: ${err.message}`);
      await page
        .screenshot({
          path: `tests/browser/screenshots/mobile-user${userId}-error.png`,
          fullPage: true,
        })
        .catch(() => {});
      return { userId, status: "fail", error: err.message, timings };
    } finally {
      await ctx.close();
    }
  }

  // --- Chạy 10 session song song ---
  const overallStart = Date.now();

  const results = await Promise.all(
    Array.from({ length: TOTAL_USERS }, (_, i) => runOneSession(i + 1)),
  );

  const overallMs = Date.now() - overallStart;

  // --- Tổng hợp kết quả ---
  const passed = results.filter((r) => r.status === "ok");
  const failed = results.filter((r) => r.status === "fail");

  console.log("\n========== KẾT QUẢ ==========");
  console.log(`Tổng thời gian chạy song song : ${overallMs}ms`);
  console.log(`Thành công : ${passed.length}/${TOTAL_USERS}`);
  console.log(`Thất bại   : ${failed.length}/${TOTAL_USERS}`);

  if (passed.length > 0) {
    const avg = (key) =>
      Math.round(
        passed.reduce((s, r) => s + (r.timings[key] ?? 0), 0) / passed.length,
      );
    const max = (key) => Math.max(...passed.map((r) => r.timings[key] ?? 0));
    const min = (key) => Math.min(...passed.map((r) => r.timings[key] ?? 0));

    console.log("\n--- Thống kê (ms) ---");
    console.log(
      `${"Bước".padEnd(12)} ${"Avg".padStart(7)} ${"Min".padStart(7)} ${"Max".padStart(7)}`,
    );
    for (const key of ["login", "pageLoad", "save", "total"]) {
      console.log(
        `${key.padEnd(12)} ${String(avg(key)).padStart(7)} ${String(min(key)).padStart(7)} ${String(max(key)).padStart(7)}`,
      );
    }

    // Chi tiết từng user
    console.log("\n--- Chi tiết từng user ---");
    results.forEach((r) => {
      const icon = r.status === "ok" ? "✓" : "✗";
      const t = r.timings;
      console.log(
        `${icon} user${r.userId}: login=${t.login ?? "-"}ms | load=${t.pageLoad ?? "-"}ms | save=${t.save ?? "-"}ms | total=${t.total}ms${r.error ? ` | ERR: ${r.error}` : ""}`,
      );
    });
  }

  if (failed.length > 0) {
    console.log("\n--- Users thất bại ---");
    failed.forEach((r) => console.log(`  user${r.userId}: ${r.error}`));
  }

  console.log("==============================\n");

  // Assert ít nhất 8/10 thành công
  expect(passed.length).toBeGreaterThanOrEqual(8);
});

test("mobile - 10 users bam diem danh va luu (concurrent + timing)", async ({
  browser,
}) => {
  const TOTAL_USERS = 10;

  // --- Helper: chạy 1 phiên điểm danh ---
  async function runOneSession(userId) {
    const start = Date.now();
    const ctx = await browser.newContext({
      ...iPhone,
      viewport: { width: 390, height: 844 },
    });
    const page = await ctx.newPage();

    page.on("console", (msg) =>
      console.log(`[user${userId}][${msg.type()}] ${msg.text()}`),
    );
    page.on("pageerror", (err) =>
      console.error(`[user${userId}][pageerror] ${err.message}`),
    );

    const timings = {};

    try {
      // 1. Login
      let t = Date.now();
      await login(page);
      timings.login = Date.now() - t;
      console.log(`[user${userId}] Đăng nhập xong (${timings.login}ms)`);

      // 2. Vào trang
      t = Date.now();
      await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);
      timings.pageLoad = Date.now() - t;
      console.log(`[user${userId}] Đã vào trang (${timings.pageLoad}ms)`);

      // 3. Emit mobile mode
      await page
        .waitForFunction(
          () =>
            typeof window.Livewire !== "undefined" ||
            typeof window.livewire !== "undefined",
          { timeout: 15000 },
        )
        .catch(() =>
          console.log(`[user${userId}][warn] Livewire không tìm thấy`),
        );

      await page.evaluate(() => {
        const emit = window.Livewire || window.livewire;
        if (emit) emit.emit("viewModeDetected", "mobile");
      });

      await page.waitForTimeout(2000);
      await waitForLivewireIdle(page);

      // 4. Chờ danh sách học sinh
      await page.waitForSelector('.lg\\:hidden button[aria-label="Có mặt"]', {
        timeout: 15000,
      });

      const studentCount = await page
        .locator('.lg\\:hidden button[aria-label="Có mặt"]')
        .count();
      console.log(`[user${userId}] Số học sinh = ${studentCount}`);

      // 5. Tap có mặt học sinh 1
      await page
        .locator('.lg\\:hidden button[aria-label="Có mặt"]')
        .first()
        .tap();

      await page.waitForTimeout(300);
      const isGreen = await page.evaluate(() => {
        const btn = document.querySelector(
          '.lg\\:hidden button[aria-label="Có mặt"]',
        );
        return btn?.classList.contains("bg-green-500");
      });
      console.log(`[user${userId}] Nút có mặt xanh = ${isGreen}`);

      // 6. Tap vắng không phép học sinh 2
      await page
        .locator('.lg\\:hidden button[aria-label="Vắng không phép"]')
        .nth(1)
        .tap();

      await page.waitForTimeout(300);
      const draftCount = await page
        .locator('button:has-text("Lưu điểm danh") span')
        .last()
        .textContent()
        .catch(() => "không thấy badge");
      console.log(`[user${userId}] Draft count = ${draftCount}`);

      // 7. Bấm lưu + đo thời gian lưu
      t = Date.now();
      await page.locator('button:has-text("Lưu điểm danh")').last().tap();

      await expect(page.locator("text=Đã lưu")).toBeVisible({ timeout: 8000 });
      timings.save = Date.now() - t;
      console.log(`[user${userId}] Toast Đã lưu (${timings.save}ms)`);

      // 8. Kiểm tra draft bị xóa
      await page.waitForTimeout(500);
      const draftAfter = await page
        .locator('button:has-text("Lưu điểm danh") span')
        .last()
        .textContent()
        .catch(() => "0");
      console.log(`[user${userId}] Draft sau lưu = ${draftAfter}`);

      // 9. Screenshot
      await page.screenshot({
        path: `tests/browser/screenshots/mobile-user${userId}-sau-khi-luu.png`,
        fullPage: true,
      });

      timings.total = Date.now() - start;
      return { userId, status: "ok", timings };
    } catch (err) {
      timings.total = Date.now() - start;
      console.error(`[user${userId}] LỖI: ${err.message}`);
      await page
        .screenshot({
          path: `tests/browser/screenshots/mobile-user${userId}-error.png`,
          fullPage: true,
        })
        .catch(() => {});
      return { userId, status: "fail", error: err.message, timings };
    } finally {
      await ctx.close();
    }
  }

  // --- Chạy 10 session song song ---
  const overallStart = Date.now();

  const results = await Promise.all(
    Array.from({ length: TOTAL_USERS }, (_, i) => runOneSession(i + 1)),
  );

  const overallMs = Date.now() - overallStart;

  // --- Tổng hợp kết quả ---
  const passed = results.filter((r) => r.status === "ok");
  const failed = results.filter((r) => r.status === "fail");

  console.log("\n========== KẾT QUẢ ==========");
  console.log(`Tổng thời gian chạy song song : ${overallMs}ms`);
  console.log(`Thành công : ${passed.length}/${TOTAL_USERS}`);
  console.log(`Thất bại   : ${failed.length}/${TOTAL_USERS}`);

  if (passed.length > 0) {
    const avg = (key) =>
      Math.round(
        passed.reduce((s, r) => s + (r.timings[key] ?? 0), 0) / passed.length,
      );
    const max = (key) => Math.max(...passed.map((r) => r.timings[key] ?? 0));
    const min = (key) => Math.min(...passed.map((r) => r.timings[key] ?? 0));

    console.log("\n--- Thống kê (ms) ---");
    console.log(
      `${"Bước".padEnd(12)} ${"Avg".padStart(7)} ${"Min".padStart(7)} ${"Max".padStart(7)}`,
    );
    for (const key of ["login", "pageLoad", "save", "total"]) {
      console.log(
        `${key.padEnd(12)} ${String(avg(key)).padStart(7)} ${String(min(key)).padStart(7)} ${String(max(key)).padStart(7)}`,
      );
    }

    // Chi tiết từng user
    console.log("\n--- Chi tiết từng user ---");
    results.forEach((r) => {
      const icon = r.status === "ok" ? "✓" : "✗";
      const t = r.timings;
      console.log(
        `${icon} user${r.userId}: login=${t.login ?? "-"}ms | load=${t.pageLoad ?? "-"}ms | save=${t.save ?? "-"}ms | total=${t.total}ms${r.error ? ` | ERR: ${r.error}` : ""}`,
      );
    });
  }

  if (failed.length > 0) {
    console.log("\n--- Users thất bại ---");
    failed.forEach((r) => console.log(`  user${r.userId}: ${r.error}`));
  }

  console.log("==============================\n");

  // Assert ít nhất 8/10 thành công
  expect(passed.length).toBeGreaterThanOrEqual(8);
});

test("mobile - 100 nguoi theo dot", async ({ browser }) => {
  const TOTAL = 100;
  const DOT_SIZE = 10; // mỗi đợt 10 người
  const results = [];

  for (let dot = 0; dot < TOTAL / DOT_SIZE; dot++) {
    console.log(`[test] Đợt ${dot + 1}/${TOTAL / DOT_SIZE}`);

    const contexts = await Promise.all(
      Array(DOT_SIZE)
        .fill(null)
        .map(() =>
          browser.newContext({
            ...iPhone,
            viewport: { width: 390, height: 844 },
          }),
        ),
    );

    const pages = await Promise.all(contexts.map((ctx) => ctx.newPage()));

    // Đăng nhập tuần tự
    for (const page of pages) {
      await login(page);
    }

    // Vào trang cùng lúc
    const dotResults = await Promise.all(
      pages.map(async (page, i) => {
        try {
          await page.goto(`${URL}/attendance?classId=${CLASS_ID}`, {
            timeout: 60000,
          });
          await waitForLivewireIdle(page);

          const has500 = await page
            .locator("text=500")
            .isVisible()
            .catch(() => false);

          const userNum = dot * DOT_SIZE + i + 1;
          console.log(`User ${userNum}: ${has500 ? "LỖI" : "OK"}`);

          return !has500;
        } catch (e) {
          const userNum = dot * DOT_SIZE + i + 1;
          console.log(`User ${userNum} lỗi: ${e.message}`);
          return false;
        }
      }),
    );

    results.push(...dotResults);

    // Đóng đợt này trước khi mở đợt tiếp
    await Promise.all(contexts.map((ctx) => ctx.close()));

    // Nghỉ 2 giây giữa các đợt
    await new Promise((r) => setTimeout(r, 5000));
  }

  // Tổng kết
  const passed = results.filter(Boolean).length;
  const failed = results.filter((r) => !r).length;
  console.log("================================");
  console.log(`[test] TỔNG: ${TOTAL} người`);
  console.log(`[test] PASS: ${passed}`);
  console.log(`[test] FAIL: ${failed}`);
  console.log("================================");

  expect(failed).toBe(0);
});

test("mobile - do thoi gian tai trang", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  await login(page);
  console.log("[test] Đăng nhập xong");

  // Bắt đầu đo
  const start = Date.now();

  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);

  // Mốc 1: trang bắt đầu hiện
  const timeToFirstByte = Date.now() - start;
  console.log(`[perf] Time to first byte: ${timeToFirstByte}ms`);

  // Emit mobile
  await page
    .waitForFunction(
      () =>
        typeof window.Livewire !== "undefined" ||
        typeof window.livewire !== "undefined",
      { timeout: 15000 },
    )
    .catch(() => {});

  await page.evaluate(() => {
    const emit = window.Livewire || window.livewire;
    if (emit) emit.emit("viewModeDetected", "mobile");
  });

  // Mốc 2: chờ Livewire render xong
  await waitForLivewireIdle(page);
  const timeToLivewire = Date.now() - start;
  console.log(`[perf] Livewire idle: ${timeToLivewire}ms`);

  // Mốc 3: chờ date selector hiện — đây là lúc user thấy được UI
  await page
    .locator('button[wire\\:click*="selectDate"]')
    .first()
    .waitFor({ timeout: 15000 });
  const timeToInteractive = Date.now() - start;
  console.log(`[perf] UI sẵn sàng dùng: ${timeToInteractive}ms`);

  // Mốc 4: chờ danh sách học sinh hiện
  await page
    .locator('.lg\\:hidden button[aria-label="Có mặt"]')
    .first()
    .waitFor({ timeout: 15000 });
  const timeToStudents = Date.now() - start;
  console.log(`[perf] Danh sách học sinh hiện: ${timeToStudents}ms`);

  // Tổng kết
  console.log("================================");
  console.log(`[perf] TỔNG KẾT (mobile)`);
  console.log(`[perf] First byte:    ${timeToFirstByte}ms`);
  console.log(`[perf] Livewire idle: ${timeToLivewire}ms`);
  console.log(`[perf] UI tương tác:  ${timeToInteractive}ms`);
  console.log(`[perf] Học sinh load: ${timeToStudents}ms`);
  console.log("================================");

  // Ngưỡng chấp nhận được — mạng nhà thờ chậm nên để 5 giây
  expect(timeToInteractive).toBeLessThan(5000);
  expect(timeToStudents).toBeLessThan(8000);

  await page.screenshot({
    path: "tests/browser/screenshots/mobile-perf.png",
    fullPage: true,
  });

  await ctx.close();
});

test("mobile - thiet bi trung binh mang 4G", async ({ browser }) => {
  const ctx = await browser.newContext({
    ...iPhone,
    viewport: { width: 390, height: 844 },
  });
  const page = await ctx.newPage();

  // Giả lập thiết bị tầm trung
  await emulateSlowDevice(page);

  await login(page);

  const start = Date.now();
  await page.goto(`${URL}/attendance?classId=${CLASS_ID}`);

  await page
    .waitForFunction(
      () =>
        typeof window.Livewire !== "undefined" ||
        typeof window.livewire !== "undefined",
      { timeout: 30000 },
    )
    .catch(() => {});

  await page.evaluate(() => {
    const emit = window.Livewire || window.livewire;
    if (emit) emit.emit("viewModeDetected", "mobile");
  });

  await waitForLivewireIdle(page);
  const timeToLivewire = Date.now() - start;

  await page
    .locator('.lg\\:hidden button[aria-label="Có mặt"]')
    .first()
    .waitFor({ timeout: 30000 });
  const timeToStudents = Date.now() - start;

  console.log("================================");
  console.log("[perf] THIẾT BỊ TRUNG BÌNH - MẠNG 4G");
  console.log(`[perf] Livewire idle: ${timeToLivewire}ms`);
  console.log(`[perf] Học sinh load: ${timeToStudents}ms`);
  console.log("================================");

  // Ngưỡng cho thiết bị yếu — cho phép chậm hơn
  expect(timeToStudents).toBeLessThan(10000); // 10 giây

  await page.screenshot({
    path: "tests/browser/screenshots/mobile-slow-device.png",
    fullPage: true,
  });

  await ctx.close();
});
