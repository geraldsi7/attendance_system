<script>
import Confirm from "@/Components/Confirm.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import ReceiptImage from "@/Components/ReceiptImage.vue";

export default {
  components: {
    AuthenticatedLayout,
    Head,
    useForm,
    Confirm,
    ReceiptImage,
  },
  props: {
    errors: Object,
    countries: Object,
    regions: Object,
    purchases: Object,
    activities: Object,
    products: Object,
    today_date: String
  },
  data() {
    const purchasesForm = useForm({
      id: null,
      issue_date: null,
      update: false,
      branch_id: "",
      product_id: "",
      title: null,
      quantity: null,
      amount: null,
      receipt: "",
    });

    const purchasesFilter = useForm({
      from_date: null,
      to_date: null,
      country_id: this.countries[0].id,
      branch_id: "",
      product_id: "",
    });

    const activityFilter = useForm({
      from_date: null,
      to_date: null,
    });

    const weeklyPayForm = useForm({
      id: null,
      issue_date: null,
      update: false,
      branch_id: "",
      title: "",
      quantity: null,
      amount: null,
      total: "",
      receipt: "",
    });

    const weeklyPayFilter = useForm({
      from_date: null,
      to_date: null,
      country_id: this.countries[0].id,
      branch_id: "",
      product_id: "",
    });

    const monthlyPayForm = useForm({
      id: null,
      issue_date: null,
      update: false,
      branch_id: "",
      title: "",
      quantity: null,
      amount: null,
      total: "",
      receipt: "",
    });

    const monthlyPayFilter = useForm({
      from_date: null,
      to_date: null,
      country_id: this.countries[0].id,
      branch_id: "",
    });

    return {
      branches: [],
      init_regions: [],
      currency: null,

      dataMethod: null,
      editContent: false,

      purchasesFilter,
      purchasesForm,
      purchasesReceipt: null,
      purchaseModal: false,
      deletePurchasesModal: false,
      selectedPurchasesRow: null,

      activityFilter,
      deleteActivitiesModal: false,
      selectedActivitiesRow: null,

      weeklyPayFilter,
      weekly_pay_branches: [],
      weeklyPayForm,
      weeklyPayReceipt: null,
      weeklyPayModal: false,
      deleteWeeklyPayModal: false,
      selectedWeeklyPayRow: null,

      monthlyPayFilter,
      monthly_pay_branches: [],
      monthlyPayForm,
      monthlyPayReceipt: null,
      monthlyPayModal: false,
      deleteMonthlyPayModal: false,
      selectedMonthlyPayRow: null,
    };
  },
  mounted() {
    this.fetchPurchases();
    this.fetchActivities();
    this.fetchWeeklyPays();
    this.fetchMonthlyPays();
    

    $(document).on("click", ".editPurchases", (evt) => {
      const data = $(evt.target).data("logId");
      this.editPurchases(data);
    });
    

    $(document).on("click", ".deletePurchases", (evt) => {
      const data = $(evt.target).data("logId");
      this.deletePurchases(data);
    });

    $(document).on("click", ".deleteActivities", (evt) => {
      const data = $(evt.target).data("logId");
      this.deleteActivities(data);
    });

    // weekly pay mounts

    $(document).on("click", ".editWeeklyPay", (evt) => {
      const data = $(evt.target).data("logId");
      this.editWeeklyPay(data);
    });

    $(document).on("click", ".deleteWeeklyPay", (evt) => {
      const data = $(evt.target).data("logId");
      this.deleteWeeklyPay(data);
    });

    // end of weekly pay mounts

    // monthly pay mounts 

    $(document).on("click", ".editMonthlyPay", (evt) => {
      const data = $(evt.target).data("logId");
      this.editMonthlyPay(data);
    });

    $(document).on("click", ".deleteMonthlyPay", (evt) => {
      const data = $(evt.target).data("logId");
      this.deleteMonthlyPay(data);
    });

    // end of monthly pay mounts
  },
  methods: {
    create() {
      this.dataMethod = "add";
      this.showPurchaseModal();
    },
    editPurchases(data) {
      this.dataMethod = "edit";
      this.editContent = true;

      axios
        .get(route("purchases.edit"), {
          params: {
            id: data,
          },
        })
        .then((response) => [
          (this.purchasesForm = useForm(Object.assign({}, response.data.row))),
          (this.purchasesReceipt = response.data.img),
          (this.init_regions = response.data.regions),
          (this.currency = response.data.currency),
        ])
        .catch((error) => console.log(error));

      this.showPurchaseModal();
    },
    showPurchaseModal() {
      this.purchaseModal = true;
    },
    hidePurchaseModal() {
      this.purchaseModal = false;
      this.reset();
    },
    deletePurchases(data) {
      this.showDeletePurchasesModal();
      this.selectedPurchasesRow = data;
    },
    showDeletePurchasesModal() {
      this.deletePurchasesModal = true;
    },
    hideDeletePurchasesModal() {
      this.deletePurchasesModal = false;
    },
    downloadPurchasesPDF(){
      axios.get(route("purchases.download"), {
              params:{
                from_date: this.purchasesFilter.from_date,
              to_date: this.purchasesFilter.to_date,
              country_id: this.purchasesFilter.country_id,
              branch_id: this.purchasesFilter.branch_id,
              }            
        })
        .catch((error) => console.log(error));
    },


    // weekly pay methods

    createWeeklyPay() {
      this.dataMethod = "add";
      this.showWeeklyPayModal();
    },
    editWeeklyPay(data) {
      this.dataMethod = "edit";
      this.editContent = true;

      axios
        .get(route("weeklypays.edit"), {
          params: {
            id: data,
          },
        })
        .then((response) => [
          (this.weeklyPayForm = useForm(Object.assign({}, response.data.row))),
          (this.weeklyPayReceipt = response.data.img),
          (this.init_regions = response.data.regions),
          (this.currency = response.data.currency),
        ])
        .catch((error) => console.log(error));

      this.showWeeklyPayModal();
    },
    showWeeklyPayModal() {
      this.weeklyPayModal = true;
    },
    hideWeeklyPayModal() {
      this.weeklyPayModal = false;
      this.resetWeeklyPay();
    },
    deleteWeeklyPay(data) {
      this.showDeleteWeeklyPayModal();
      this.selectedWeeklyPayRow = data;
    },
    showDeleteWeeklyPayModal() {
      this.deleteWeeklyPayModal = true;
    },
    hideDeleteWeeklyPayModal() {
      this.deleteWeeklyPayModal = false;
    },
    resetWeeklyPay() {
      this.weeklyPayForm = useForm({
      id: null,
      issue_date: null,
      update: false,
      branch_id: "",
      title: "",
      quantity: null,
      amount: null,
      total: "",
      receipt: "",
    });

      this.weeklyPayReceipt = null;
      this.init_regions = [];
      this.currency = null;

      this.clearErrors();
      this.editContent = false;
    },
    fetchWeeklyPayBranches() {
      axios
        .get(route("branches.fetch"), {
          params: {
            id: this.weeklyPayFilter.country_id,
          },
        })
        .then((response) => [
          (this.weekly_pay_branches = response.data.row),
          (this.weeklyPayFilter.branch_id = ""),
        ])
        .catch((error) => console.log(error));
    },

    fetchWeeklyPays() {
      if (
        Date.parse(this.weeklyPayFilter.from_date) >
        Date.parse(this.weeklyPayFilter.to_date)
      ) {
        toastr.error(
          "Invalid Date Range!<br>Start Date cannot be after End Date!"
        );
      } else {
        $("#weekly_pays_table").DataTable({
          destroy: true,
          stateSave: true,
          processing: false,
          serverSide: true,
          ajax: {
            url: route("weeklypays.fetch"),
            data: {
              from_date: this.weeklyPayFilter.from_date,
              to_date: this.weeklyPayFilter.to_date,
              country_id: this.weeklyPayFilter.country_id,
              branch_id: this.weeklyPayFilter.branch_id,
            },
          },
          columns: [
            { data: "issue_date", name: "issue_date" },
            { data: "reference", name: "reference" },
            { data: "quantity", name: "quantity" },
            { data: "amount", name: "amount" },
            { data: "total", name: "total" },
            {
              data: "action",
              name: "action",
              orderable: false,
              searchable: false,
            },
          ],
          columnDefs: [
            { className: "text-right", targets: [2, 3, 4] },
            { width: "35%", targets: 5 },
            { width: "20%", targets: 1 },
            { width: "10%", targets: [0, 2, 3, 4] },
          ],
          lengthMenu: [
            [10, 25, 50, 100, -1],
            ["10", "25", "50", "100", "All"],
          ],
          order: [["0", "desc"]],
          drawCallback: function () {
            var api = this.api();
            var json = api.ajax.json();

            var intVal = function (i) {
              return typeof i === "string"
                ? i.replace(/[\$,]/g, "") * 1
                : typeof i === "number"
                ? i
                : 0;
            };

            // Total Quantity over all pages
            var totalQty = json.totalQty;

            // Total Quantity on this page
            var pageTotalQty = api
              .column(2, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Total Amount over all pages
            var totalAmount = json.totalAmount;

            // Total Amount over this page
            var pageTotalAmount = api
              .column(3, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Grand Total Amount over all pages
            var grandTotalAmount = json.grandTotal;

            // Grand Total Amount over this page
            var pageGrandTotalAmount = api
              .column(4, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            pageTotalQty = Number(pageTotalQty).toLocaleString();
            pageTotalAmount = Number(pageTotalAmount).toLocaleString();
            pageGrandTotalAmount =
              Number(pageGrandTotalAmount).toLocaleString();

            $(api.column(0).footer()).html("Total");

            $(api.column(2).footer()).html(
              pageTotalQty + " (" + totalQty + " total)"
            );

            $(api.column(3).footer()).html(
              json.currency_code +
                " " +
                pageTotalAmount +
                " (" +
                json.currency_code +
                " " +
                totalAmount +
                " total)"
            );

            $(api.column(4).footer()).html(
              json.currency_code +
                " " +
                pageGrandTotalAmount +
                " (" +
                json.currency_code +
                " " +
                grandTotalAmount +
                " total)"
            );
          },
        });
      }
    },

    submitWeeklyPay() {
      if (!this.editContent) {
        this.weeklyPayForm.post(route("weeklypays.store"), {
          forceFormData: true,
          onSuccess: () => {
            this.hideWeeklyPayModal();
            toastr.success("Record successfully saved");
            this.fetchActivities();
            this.fetchWeeklyPays();
          },
          onError: (errors) => {
            toastr.error("Something went wrong");
          },
        });
      } else {
        this.weeklyPayForm.post(
          route("weeklypays.update", this.weeklyPayForm.id),
          {
            forceFormData: true,
            _method: "PUT",
            receipt: this.weeklyPayForm.receipt,
            updateTy: this.weeklyPayForm.update,
            onSuccess: () => {
              this.hideWeeklyPayModal();
              toastr.success("Record successfully updated");
              this.fetchActivities();
              this.fetchWeeklyPays();
            },
            onError: (errors) => {
              toastr.error("Something went wrong");
            },
          }
        );
      }
    },

    destroyWeeklyPay(data) {
      axios.post(route("weeklypays.destroy"), {
          id: data,
        })
        .then(
          this.hideDeleteWeeklyPayModal(),
          toastr.success("Record successfully deleted"),
          this.fetchActivities(),
        this.fetchWeeklyPays(),
        )
        .catch((error) => console.log(error));
    },

    calculateWeeklyPayAmount() {
      this.weeklyPayForm.total =
        this.weeklyPayForm.quantity * this.weeklyPayForm.amount;
    },

    downloadWeeklyPayPDF(){
      axios.get(route("weeklypays.download"), {
              params:{
                from_date: this.weeklyPayFilter.from_date,
              to_date: this.weeklyPayFilter.to_date,
              country_id: this.weeklyPayFilter.country_id,
              branch_id: this.weeklyPayFilter.branch_id,
              }            
        })
        .catch((error) => console.log(error));
    },
    // end of weekly Pay methods

    // monthly pay methods

createMonthlyPay() {
      this.dataMethod = "add";
      this.showMonthlyPayModal();
    },

    editMonthlyPay(data) {
      this.dataMethod = "edit";
      this.editContent = true;

      axios
        .get(route("monthlypays.edit"), {
          params: {
            id: data,
          },
        })
        .then((response) => [
          (this.monthlyPayForm = useForm(Object.assign({}, response.data.row))),
          (this.monthlyPayReceipt = response.data.img),
          (this.init_regions = response.data.regions),
          (this.currency = response.data.currency),
        ])
        .catch((error) => console.log(error));

      this.showMonthlyPayModal();
    },

    showMonthlyPayModal() {
      this.monthlyPayModal = true;
    },

    hideMonthlyPayModal() {
      this.monthlyPayModal = false;
      this.resetMonthlyPay();
    },
    resetMonthlyPay() {
      this.monthlyPayForm = useForm({
      id: null,
      issue_date: null,
      update: false,
      branch_id: "",
      title: "",
      quantity: null,
      amount: null,
      total: "",
      receipt: "",
    });

      this.monthlyPayReceipt = null;
      this.init_regions = [];
      this.currency = null;

      this.clearErrors();
      this.editContent = false;
    },

    deleteMonthlyPay(data) {
      this.showDeleteMonthlyPayModal();
      this.selectedMonthlyPayRow = data;
    },

    showDeleteMonthlyPayModal() {
      this.deleteMonthlyPayModal = true;
    },

    hideDeleteMonthlyPayModal() {
      this.deleteMonthlyPayModal = false;
    },
    
    fetchMonthlyPayBranches() {
      axios
        .get(route("branches.fetch"), {
          params: {
            id: this.monthlyPayFilter.country_id,
          },
        })
        .then((response) => [
          (this.monthly_pay_branches = response.data.row),
          (this.monthlyPayFilter.branch_id = ""),
        ])
        .catch((error) => console.log(error));
    },

    fetchMonthlyPays() {
      if (
        Date.parse(this.monthlyPayFilter.from_date) >
        Date.parse(this.monthlyPayFilter.to_date)
      ) {
        toastr.error(
          "Invalid Date Range!<br>Start Date cannot be after End Date!"
        );
      } else {
        $("#monthly_pays_table").DataTable({
          destroy: true,
          stateSave: true,
          processing: false,
          serverSide: true,
          ajax: {
            url: route("monthlypays.fetch"),
            data: {
              from_date: this.monthlyPayFilter.from_date,
              to_date: this.monthlyPayFilter.to_date,
              country_id: this.monthlyPayFilter.country_id,
              branch_id: this.monthlyPayFilter.branch_id,
            },
          },
          columns: [
            { data: "issue_date", name: "issue_date" },
            { data: "reference", name: "reference" },
            { data: "quantity", name: "quantity" },
            { data: "amount", name: "amount" },
            { data: "total", name: "total" },
            {
              data: "action",
              name: "action",
              orderable: false,
              searchable: false,
            },
          ],
          columnDefs: [
            { className: "text-right", targets: [2, 3, 4] },
            { width: "35%", targets: 5 },
            { width: "20%", targets: 1 },
            { width: "10%", targets: [0, 2, 3, 4] },
          ],
          lengthMenu: [
            [10, 25, 50, 100, -1],
            ["10", "25", "50", "100", "All"],
          ],
          order: [["0", "desc"]],
          drawCallback: function () {
            var api = this.api();
            var json = api.ajax.json();

            var intVal = function (i) {
              return typeof i === "string"
                ? i.replace(/[\$,]/g, "") * 1
                : typeof i === "number"
                ? i
                : 0;
            };

            // Total Quantity over all pages
            var totalQty = json.totalQty;

            // Total Quantity on this page
            var pageTotalQty = api
              .column(2, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Total Amount over all pages
            var totalAmount = json.totalAmount;

            // Total Amount over this page
            var pageTotalAmount = api
              .column(3, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Grand Total Amount over all pages
            var grandTotalAmount = json.grandTotal;

            // Grand Total Amount over this page
            var pageGrandTotalAmount = api
              .column(4, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            pageTotalQty = Number(pageTotalQty).toLocaleString();
            pageTotalAmount = Number(pageTotalAmount).toLocaleString();
            pageGrandTotalAmount =
              Number(pageGrandTotalAmount).toLocaleString();

            $(api.column(0).footer()).html("Total");

            $(api.column(2).footer()).html(
              pageTotalQty + " (" + totalQty + " total)"
            );

            $(api.column(3).footer()).html(
              json.currency_code +
                " " +
                pageTotalAmount +
                " (" +
                json.currency_code +
                " " +
                totalAmount +
                " total)"
            );

            $(api.column(4).footer()).html(
              json.currency_code +
                " " +
                pageGrandTotalAmount +
                " (" +
                json.currency_code +
                " " +
                grandTotalAmount +
                " total)"
            );
          },
        });
      }
    },

    submitMonthlyPay() {
      if (!this.editContent) {
        this.monthlyPayForm.post(route("monthlypays.store"), {
          forceFormData: true,
          onSuccess: () => {
            this.hideMonthlyPayModal();
            toastr.success("Record successfully saved");
            this.fetchActivities();
            this.fetchMonthlyPays();
          },
          onError: (errors) => {
            toastr.error("Something went wrong");
          },
        });
      } else {
        this.monthlyPayForm.post(
          route("monthlypays.update", this.monthlyPayForm.id),
          {
            forceFormData: true,
            _method: "PUT",
            receipt: this.monthlyPayForm.receipt,
            updateTy: this.monthlyPayForm.update,
            onSuccess: () => {
              this.hideMonthlyPayModal();
              toastr.success("Record successfully updated");
              this.fetchActivities();
              this.fetchMonthlyPays();
            },
            onError: (errors) => {
              toastr.error("Something went wrong");
            },
          }
        );
      }
    },

    destroyMonthlyPay(data) {
      axios.post(route("monthlypays.destroy"), {
          id: data,
        })
        .then(
          this.hideDeleteMonthlyPayModal(),
          toastr.success("Record successfully deleted"),
          this.fetchActivities(),
        this.fetchMonthlyPays(),
        )
        .catch((error) => console.log(error));
    },

    calculateMonthlyPayAmount() {
      this.monthlyPayForm.total =
        this.monthlyPayForm.quantity * this.monthlyPayForm.amount;
    },

    downloadMonthlyPayPDF(){
      axios.get(route("monthlypays.download"), {
              params:{
                from_date: this.monthlyPayFilter.from_date,
              to_date: this.monthlyPayFilter.to_date,
              country_id: this.monthlyPayFilter.country_id,
              branch_id: this.monthlyPayFilter.branch_id,
              }            
        })
        .catch((error) => console.log(error));
    },
    // end of monthly pay methods

    deleteActivities(data) {
      this.showDeleteActivitiesModal();
      this.selectedActivitiesRow = data;
    },
    showDeleteActivitiesModal() {
      this.deleteActivitiesModal = true;
    },
    hideDeleteActivitiesModal() {
      this.deleteActivitiesModal = false;
    },

    downloadActivitiesPDF(){
      axios.get(route("activities.download"), {
              params:{
                from_date: this.activityFilter.from_date,
              to_date: this.activityFilter.to_date,
              }            
        })
        .catch((error) => console.log(error));
    },
    reset() {
      this.purchasesForm = useForm({
        id: null,
        update: false,
        issue_date: null,
        branch_id: "",
        product_id: "",
        title: null,
        quantity: null,
        amount: null,
        receipt: "",
      });

      this.purchasesReceipt = null;
      this.init_regions = [];
      this.currency = null;

      this.clearErrors();
      this.editContent = false;
    },

    clearErrors() {
      Object.keys(this.errors).forEach((field) => delete this.errors[field]);
    },

    fetchBranches() {
      axios
        .get(route("branches.fetch"), {
          params: {
            id: this.purchasesFilter.country_id,
          },
        })
        .then((response) => [
          (this.branches = response.data.row),
          (this.purchasesFilter.branch_id = ""),
        ])
        .catch((error) => console.log(error));
    },

    fetchPurchases() {
      if (
        Date.parse(this.purchasesFilter.from_date) >
        Date.parse(this.purchasesFilter.to_date)
      ) {
        toastr.error(
          "Invalid Date Range!<br>Start Date cannot be after End Date!"
        );
      } else {
        $("#purchases_table").DataTable({
          destroy: true,
          stateSave: true,
          processing: false,
          serverSide: true,
          ajax: {
            url: route("purchases.fetch"),
            data: {
              from_date: this.purchasesFilter.from_date,
              to_date: this.purchasesFilter.to_date,
              country_id: this.purchasesFilter.country_id,
              branch_id: this.purchasesFilter.branch_id,
            },
          },
          columns: [
            { data: "issue_date", name: "issue_date" },
            { data: "reference", name: "reference" },
            { data: "quantity", name: "quantity" },
            { data: "amount", name: "amount" },
            {
              data: "action",
              name: "action",
              orderable: false,
              searchable: false,
            },
          ],
          columnDefs: [
            { className: "text-right", targets: [2, 3] },
            { width: "35%", targets: 4 },
            { width: "20%", targets: 1 },
            { width: "15%", targets: [0, 2, 3] },
          ],
          lengthMenu: [
            [10, 25, 50, 100, -1],
            ["10", "25", "50", "100", "All"],
          ],
          order: [["0", "desc"]],
          drawCallback: function () {
            var api = this.api();
            var json = api.ajax.json();

            var intVal = function (i) {
              return typeof i === "string"
                ? i.replace(/[\$,]/g, "") * 1
                : typeof i === "number"
                ? i
                : 0;
            };

            // Total Quantity over all pages
            var totalQty = json.totalQty;

            // Total Quantity on this page
            var pageTotalQty = api
              .column(2, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Total Amount over all pages
            var totalAmount = json.totalAmount;

            // Total Amount over this page
            var pageTotalAmount = api
              .column(3, { page: "current" })
              .data()
              .reduce(function (a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            pageTotalQty = Number(pageTotalQty).toLocaleString();
            pageTotalAmount = Number(pageTotalAmount).toLocaleString();

            $(api.column(0).footer()).html("Total");

            $(api.column(2).footer()).html(
              pageTotalQty + " (" + totalQty + " total)"
            );

            $(api.column(3).footer()).html(
              json.currency_code +
                " " +
                pageTotalAmount +
                " (" +
                json.currency_code +
                " " +
                totalAmount +
                " total)"
            );
          },
        });
      }
    },
    fetchActivities() {
      if (
        Date.parse(this.activityFilter.from_date) >
        Date.parse(this.activityFilter.to_date)
      ) {
        toastr.error(
          "Invalid Date Range!<br>Start Date cannot be after End Date!"
        );
      } else {
        $("#activities_table").DataTable({
          destroy: true,
          stateSave: true,
          processing: false,
          serverSide: true,
          ajax: {
            url: route("activities.fetch"),
            data: {
              from_date: this.activityFilter.from_date,
              to_date: this.activityFilter.to_date,
            },
          },
          columns: [
            { data: "updated_at", name: "updated_at" },
            { data: "status", name: "status" },
            { data: "description", name: "description" },
            {
              data: "action",
              name: "action",
              orderable: false,
              searchable: false,
            },
          ],
          order: [["0", "desc"]],
          lengthMenu: [
            [10, 25, 50, 100, -1],
            ["10", "25", "50", "100", "All"],
          ],
        });
      }
    },

    successfulSubmission() {
      this.fetchPurchases();
      this.fetchActivities();
    },
    submitPurchases() {
      if (!this.editContent) {
        this.purchasesForm.post(route("purchases.store"), {
          forceFormData: true,
          onSuccess: () => {
            this.hidePurchaseModal();
            toastr.success("Record successfully saved");
            this.successfulSubmission();
          },
          onError: (errors) => {
            toastr.error("Something went wrong");
          },
        });
      } else {
        this.purchasesForm.post(
          route("purchases.update", this.purchasesForm.id),
          {
            forceFormData: true,
            _method: "PUT",
            receipt: this.purchasesForm.receipt,
            updateTy: this.purchasesForm.update,
            onSuccess: () => {
              this.hidePurchaseModal();
              toastr.success("Record successfully updated");
              this.successfulSubmission();
            },
            onError: (errors) => {
              toastr.error("Something went wrong");
            },
          }
        );
      }
    },

    destroyPurchases(data) {
      axios
        .post(route("purchases.destroy"), {
          id: data,
        })
        .then(
          this.hideDeletePurchasesModal(),
          toastr.success("Record successfully deleted"),
          this.successfulSubmission()
        )
        .catch((error) => console.log(error));
    },

    destroyActivities(data) {
      axios
        .post(route("activities.destroy"), {
          id: data,
        })
        .then(
          this.hideDeleteActivitiesModal(),
          toastr.success("Record successfully deleted"),
          this.fetchActivities()
        )
        .catch((error) => console.log(error));
    },
  },
};
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        v-if="$page.props.auth.user.role == 'admin'"
        class="font-semibold text-xl text-gray-800 leading-tight"
      >
        Overview
      </h2>
      <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- purchases -->
        <p class="mt-3 text-xl font-semibold text-gray-800 capitalize">
          purchases
        </p>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="md:flex md:justify-between md:items-start gap-1.5 mb-4">
              <div
                class="
                  md:flex md:flex-wrap
                  lg:flex-nowrap
                  items-center
                  gap-1.5
                  md:w-1/2
                  lg:w-2/3
                "
              >
                <select
                  v-if="$page.props.auth.user.role == 'admin'"
                  v-model="purchasesFilter.country_id"
                  @change="fetchBranches(), fetchPurchases()"
                  class="
                    form-control
                    mt-2.5
                    md:mb-2.5
                    block
                    w-full
                    lg:w-1/5
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                >
                  <option value="" disabled>-- Select country --</option>
                  <option
                    v-for="country in countries"
                    :key="country.id"
                    :value="country.id"
                  >
                    {{ country.name }}
                  </option>
                </select>
                <select
                  v-if="$page.props.auth.user.role == 'admin'"
                  v-model="purchasesFilter.branch_id"
                  @change="fetchPurchases"
                  class="
                    form-control
                    mt-2.5
                    md:mb-2.5
                    block
                    w-full
                    lg:w-48
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                >
                  <option value="" disabled>-- Select branch --</option>
                  <option
                    v-for="branch in branches"
                    :key="branch.id"
                    :value="branch.id"
                  >
                    {{ branch.name }}
                  </option>
                  <option value="" v-if="branches.length >= 1">None</option>
                </select>

                <input
                  v-model="purchasesFilter.from_date"
                  @change="fetchPurchases"
                  type="date"
                  id="from_date"
                  class="
                    date
                    block
                    w-full
                    md:w-2/5
                    lg:w-40
                    mt-2.5
                    md:mb-2.5
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                />
                <span class="material-symbols-outlined flex justify-center">
                  trending_flat
                </span>
                <input
                  v-model="purchasesFilter.to_date"
                  @change="fetchPurchases"
                  type="date"
                  class="
                    date
                    block
                    w-full
                    md:w-2/5
                    lg:w-40
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                />
              </div>
              
              <div>
              <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  mr-1.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="downloadPurchasesPDF"
              >
                Download PDF
              </button>
              
              <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="create('purchases')"
              >
                Add purchases
              </button>
            </div>
            </div>

            <div class="flex flex-col">
              <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                  <div class="overflow-x-auto">
                    <table id="purchases_table" class="min-w-full">
                      <thead class="border-b bg-gray-100 font-medium">
                        <tr>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Date of Issue
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Reference
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Quantity
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Amount
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Action
                          </th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal" id="manageModal" v-if="this.purchaseModal">
              <div class="modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="flex justify-between items-center">
                      <h2 class="text-xl capitalize modal-title">
                        {{ dataMethod }} purchases
                      </h2>
                      <button @click="hidePurchaseModal">
                        <span class="material-symbols-outlined">close</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body overscroll-auto">                    <form
                      @submit.prevent="submitPurchases"
                      class="mt-4 px-2 pb-2"
                      enctype="multipart/form-data"
                    >
                      <div class="row gx-3"> 
                        <div
                          class="col-12">
                          <label for="date"
                            >Date of Issue <span class="text-red-500">*</span></label
                          >
                          <input
                  v-model="purchasesForm.issue_date"
                  type="date"
                  class="
                    date
                    form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                  "
                  :max="this.today_date"
                  :class="{ 'is-invalid': errors.issue_date }"
                />
                          <div
                            v-if="errors.issue_date"
                            v-text="errors.issue_date"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="branch_id"
                            >Branch <span class="text-red-500">*</span></label
                          >
                          <select
                            id="branch_id"
                            v-if="editContent"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="purchasesForm.branch_id"
                            :class="{ 'is-invalid': errors.branch_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Branch --
                            </option>
                            <option
                              v-for="region in init_regions"
                              :key="region.id"
                              :value="region.id"
                            >
                              {{ region.name }}
                            </option>
                          </select>

                          <select
                            id="branch_id"
                            v-else
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="purchasesForm.branch_id"
                            :class="{ 'is-invalid': errors.branch_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Branch --
                            </option>
                            <option
                              else
                              v-for="region in regions"
                              :key="region.id"
                              :value="region.id"
                            >
                              {{ region.name }}
                            </option>
                          </select>
                          <div
                            v-if="errors.branch_id"
                            v-text="errors.branch_id"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="product"
                            >Item <span class="text-red-500">*</span></label
                          >
                          <select
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="purchasesForm.product_id"
                            @change="this.purchasesForm.title = null"
                            :class="{ 'is-invalid': errors.product_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Item --
                            </option>
                            <option
                              v-for="product in products"
                              :key="product.id"
                              :value="product.id"
                            >
                              {{ product.name }}
                            </option>
                            <option value="Others">Others</option>
                          </select>
                          <div
                            v-if="errors.product_id"
                            v-text="errors.product_id"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div
                          class="col-12"
                          v-if="this.purchasesForm.product_id == 'Others'"
                        >
                          <label for="title"
                            >Title <span class="text-red-500">*</span></label
                          >
                          <input
                            type="text"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="purchasesForm.title"
                            placeholder="Title"
                            :class="{ 'is-invalid': errors.title }"
                          />
                          <div
                            v-if="errors.title"
                            v-text="errors.title"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="quantity"
                            >Quantity <span class="text-red-500">*</span></label
                          >
                          <input
                            type="number"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="purchasesForm.quantity"
                            placeholder="Quantity"
                            :class="{ 'is-invalid': errors.quantity }"
                          />
                          <div
                            v-if="errors.quantity"
                            v-text="errors.quantity"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="amount"
                            >Amount <span class="text-red-500">*</span></label
                          >
                          <div class="relative">
                            <input
                              type="text"
                              id="hs-trailing-icon"
                              name="hs-trailing-icon"
                              class="
                                form-control
                                block
                                w-full
                                rounded-md
                                border border-gray-300
                                bg-white
                                py-2
                                px-3
                                shadow-sm
                                focus:border-indigo-500
                                focus:outline-none
                                focus:ring-indigo-500
                                sm:text-sm
                              "
                              v-model="purchasesForm.amount"
                              placeholder="Amount"
                              :class="{ 'is-invalid': errors.amount }"
                            />
                            <div
                              class="
                                absolute
                                inset-y-0
                                right-0
                                flex
                                items-center
                                pointer-events-none
                                z-20
                                pr-5
                              "
                            >
                              <span class="text-gray-500">{{
                                this.editContent
                                  ? this.currency
                                  : $page.props.auth.country.currency_code
                              }}</span>
                            </div>
                            <div
                              v-if="errors.amount"
                              v-text="errors.amount"
                              class="text-red-500"
                            ></div>
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="shrink-0" v-if="editContent">
                            <ReceiptImage
                              :src="purchasesReceipt"
                              alt="Current receipt"
                            />
                          </div>
                          <div
                            class="flex items-center mb-3"
                            v-if="editContent"
                          >
                            <input
                              type="checkbox"
                              class="
                                default:ring-2
                                text-purple-600
                                rounded-sm
                                focus:ring-purple-500
                              "
                              @change="
                                this.purchasesForm.update =
                                  !this.purchasesForm.update
                              "
                            />
                            <p class="pl-2">Update receipt</p>
                          </div>

                          <label
                            class="block"
                            v-if="purchasesForm.update || !editContent"
                            >Receipt <span class="text-red-500">*</span>
                            <span class="sr-only">Choose receipt</span>
                            <input
                              type="file"
                              accept="image/*"
                              class="
                                block
                                w-full
                                text-sm text-slate-500
                                file:mr-4
                                file:py-2
                                file:px-4
                                file:rounded-full
                                file:border-0
                                file:text-sm
                                file:font-semibold
                                file:bg-violet-50
                                file:text-violet-700
                                hover:file:bg-violet-100
                              "
                              :class="{ 'is-invalid': errors.receipt }"
                              @input="
                                purchasesForm.receipt = $event.target.files[0]
                              "
                            />
                          </label>
                          <div
                            v-if="errors.receipt"
                            v-text="errors.receipt"
                            class="text-red-500"
                          ></div>
                        </div>

                        <button
                          class="
                            inline-block
                            px-6
                            py-2.5
                            bg-purple-600
                            text-white
                            font-medium
                            text-sm
                            leading-tight
                            capitalize
                            rounded
                            shadow-md
                            hover:bg-purple-700 hover:shadow-lg
                            focus:bg-purple-700
                            focus:shadow-lg
                            focus:outline-none
                            focus:ring-0
                            active:bg-purple-800 active:shadow-lg
                            transition
                            duration-150
                            ease-in-out
                          "
                          type="submit"
                          :disabled="purchasesForm.processing"
                          :class="{ 'opacity-25': purchasesForm.processing }"
                          v-if="!editContent"
                        >
                          Save
                        </button>
                        <button
                          class="
                            inline-block
                            px-6
                            py-2.5
                            bg-purple-600
                            text-white
                            font-medium
                            text-sm
                            leading-tight
                            capitalize
                            rounded
                            shadow-md
                            hover:bg-purple-700 hover:shadow-lg
                            focus:bg-purple-700
                            focus:shadow-lg
                            focus:outline-none
                            focus:ring-0
                            active:bg-purple-800 active:shadow-lg
                            transition
                            duration-150
                            ease-in-out
                          "
                          type="submit"
                          :disabled="purchasesForm.processing"
                          :class="{ 'opacity-25': purchasesForm.processing }"
                          v-else
                        >
                          Update
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- end of purchases form modal -->

            <div
              class="modal"
              id="manageModal"
              v-if="this.deletePurchasesModal"
            >
              <div class="modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="flex justify-between items-center">
                      <h2 class="text-xl capitalize modal-title">
                        Delete purchases
                      </h2>
                      <button @click="hideDeletePurchasesModal">
                        <span class="material-symbols-outlined">close</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body">
                    <Confirm
                      @destroy="destroyPurchases(this.selectedPurchasesRow)"
                      @close="hideDeletePurchasesModal"
                      >Are you sure you want to delete this record?</Confirm
                    >
                  </div>
                </div>
              </div>
            </div>
            <!-- end of delete purchases modal-->
          </div>
        </div>

        <!-- end of purchases -->

        <!-- weekly Pay -->
        <p class="mt-5 text-xl font-semibold text-gray-800 capitalize">
          weekly Payments
        </p>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="md:flex md:justify-between md:items-start gap-1.5 mb-4">
              <div
                class="
                  md:flex md:flex-wrap
                  lg:flex-nowrap
                  items-center
                  gap-1.5
                  md:w-1/2
                  lg:w-2/3
                "
              >
                <select
                  v-if="$page.props.auth.user.role == 'admin'"
                  v-model="weeklyPayFilter.country_id"
                  @change="fetchWeeklyPayBranches(), fetchWeeklyPays()"
                  class="
                    form-control
                    mt-2.5
                    md:mb-2.5
                    block
                    w-full
                    lg:w-1/5
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                >
                  <option value="" disabled>-- Select country --</option>
                  <option
                    v-for="country in countries"
                    :key="country.id"
                    :value="country.id"
                  >
                    {{ country.name }}
                  </option>
                </select>
                <select
                  v-if="$page.props.auth.user.role == 'admin'"
                  v-model="weeklyPayFilter.branch_id"
                  @change="fetchWeeklyPays"
                  class="
                    form-control
                    mt-2.5
                    md:mb-2.5
                    block
                    w-full
                    lg:w-48
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                >
                  <option value="" disabled>-- Select branch --</option>
                  <option
                    v-for="branch in weekly_pay_branches"
                    :key="branch.id"
                    :value="branch.id"
                  >
                    {{ branch.name }}
                  </option>
                  <option value="" v-if="weekly_pay_branches.length >= 1">
                    None
                  </option>
                </select>

                <input
                  v-model="weeklyPayFilter.from_date"
                  @change="fetchWeeklyPays"
                  type="date"
                  id="from_date"
                  class="
                    date
                    block
                    w-full
                    md:w-2/5
                    lg:w-40
                    mt-2.5
                    md:mb-2.5
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                />
                <span class="material-symbols-outlined flex justify-center">
                  trending_flat
                </span>
                <input
                  v-model="weeklyPayFilter.to_date"
                  @change="fetchWeeklyPays"
                  type="date"
                  class="
                    date
                    block
                    w-full
                    md:w-2/5
                    lg:w-40
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                />
              </div>
              <div>
              <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  mr-1.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="downloadWeeklyPayPDF"
              >
                Download PDF
              </button>

              <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="createWeeklyPay('weekly payment')"
              >
                Add payment
              </button>
              </div>
                          </div>

            <div class="flex flex-col">
              <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                  <div class="overflow-x-auto">
                    <table id="weekly_pays_table" class="min-w-full">
                      <thead class="border-b bg-gray-100 font-medium">
                        <tr>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Date of Issue
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Reference
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Quantity
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Amount
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Total
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Action
                          </th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal" id="manageModal" v-if="this.weeklyPayModal">
              <div class="modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="flex justify-between items-center">
                      <h2 class="text-xl capitalize modal-title">
                        {{ dataMethod }} weekly payment
                      </h2>
                      <button @click="hideWeeklyPayModal">
                        <span class="material-symbols-outlined">close</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body overscroll-auto">
                    <form
                      @submit.prevent="submitWeeklyPay"
                      class="mt-4 px-2 pb-2"
                      enctype="multipart/form-data"
                    >
                      <div class="row gx-3">
                        <div
                          class="col-12">
                          <label for="date"
                            >Date of Issue <span class="text-red-500">*</span></label
                          >
                          <input
                  v-model="weeklyPayForm.issue_date"
                  type="date"
                  class="
                    date
                    form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                  "
                  :max="this.today_date"
                  :class="{ 'is-invalid': errors.issue_date }"
                />
                          <div
                            v-if="errors.issue_date"
                            v-text="errors.issue_date"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="branch_id"
                            >Branch <span class="text-red-500">*</span></label
                          >
                          <select
                            id="branch_id"
                            v-if="editContent"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="weeklyPayForm.branch_id"
                            :class="{ 'is-invalid': errors.branch_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Branch --
                            </option>
                            <option
                              v-for="region in init_regions"
                              :key="region.id"
                              :value="region.id"
                            >
                              {{ region.name }}
                            </option>
                          </select>

                          <select
                            id="branch_id"
                            v-else
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="weeklyPayForm.branch_id"
                            :class="{ 'is-invalid': errors.branch_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Branch --
                            </option>
                            <option
                              else
                              v-for="region in regions"
                              :key="region.id"
                              :value="region.id"
                            >
                              {{ region.name }}
                            </option>
                          </select>
                          <div
                            v-if="errors.branch_id"
                            v-text="errors.branch_id"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="title"
                            >Title <span class="text-red-500">*</span></label
                          >
                          <input
                            type="text"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="weeklyPayForm.title"
                            placeholder="Title"
                            :class="{ 'is-invalid': errors.title }"
                          />
                          <div
                            v-if="errors.title"
                            v-text="errors.title"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="quantity"
                            >Quantity <span class="text-red-500">*</span></label
                          >
                          <input
                            type="number"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="weeklyPayForm.quantity"
                            @keyup="calculateWeeklyPayAmount"
                            placeholder="Quantity"
                            :class="{ 'is-invalid': errors.quantity }"
                          />
                          <div
                            v-if="errors.quantity"
                            v-text="errors.quantity"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="amount"
                            >Amount (per head) <span class="text-red-500">*</span></label
                          >
                          <div class="relative">
                            <input
                              type="text"
                              id="hs-trailing-icon"
                              name="hs-trailing-icon"
                              class="
                                form-control
                                block
                                w-full
                                rounded-md
                                border border-gray-300
                                bg-white
                                py-2
                                px-3
                                shadow-sm
                                focus:border-indigo-500
                                focus:outline-none
                                focus:ring-indigo-500
                                sm:text-sm
                              "
                              v-model="weeklyPayForm.amount"
                              @keyup="calculateWeeklyPayAmount"
                              placeholder="Amount"
                              :class="{ 'is-invalid': errors.amount }"
                            />
                            <div
                              class="
                                absolute
                                inset-y-0
                                right-0
                                flex
                                items-center
                                pointer-events-none
                                z-20
                                pr-5
                              "
                            >
                              <span class="text-gray-500">{{
                                this.editContent
                                  ? this.currency
                                  : $page.props.auth.country.currency_code
                              }}</span>
                            </div>
                            <div
                              v-if="errors.amount"
                              v-text="errors.amount"
                              class="text-red-500"
                            ></div>
                          </div>
                        </div>

                        <div class="col-12">
                          <label for="amount"
                            >Total <span class="text-red-500">*</span></label
                          >
                          <div class="relative">
                            <input
                              type="text"
                              id="hs-trailing-icon"
                              name="hs-trailing-icon"
                              class="
                                block
                                w-full
                                rounded-md
                                border border-gray-300
                                bg-white
                                py-2
                                px-3
                                shadow-sm
                                focus:outline-none
                                focus:border-gray-300
                                focus:ring-gray-300
                                sm:text-sm
                                read-only:bg-slate-100
                              "
                              v-model="weeklyPayForm.total"
                              placeholder="Total"
                              :readonly="true"
                            />
                            <div
                              class="
                                absolute
                                inset-y-0
                                right-0
                                flex
                                items-center
                                pointer-events-none
                                z-20
                                pr-5
                              "
                            >
                              <span class="text-gray-500">{{
                                this.editContent
                                  ? this.currency
                                  : $page.props.auth.country.currency_code
                              }}</span>
                            </div>
                            <div
                              v-if="errors.total"
                              v-text="errors.total"
                              class="text-red-500"
                            ></div>
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="shrink-0" v-if="editContent">
                            <ReceiptImage
                              :src="weeklyPayReceipt"
                              alt="Current receipt"
                            />
                          </div>
                          <div
                            class="flex items-center mb-3"
                            v-if="editContent"
                          >
                            <input
                              type="checkbox"
                              class="
                                default:ring-2
                                text-purple-600
                                rounded-sm
                                focus:ring-purple-500
                              "
                              @change="
                                this.weeklyPayForm.update =
                                  !this.weeklyPayForm.update
                              "
                            />
                            <p class="pl-2">Update receipt</p>
                          </div>

                          <label
                            class="block"
                            v-if="weeklyPayForm.update || !editContent"
                            >Receipt <span class="text-red-500">*</span>
                            <span class="sr-only">Choose receipt</span>
                            <input
                              type="file"
                              accept="image/*"
                              class="
                                block
                                w-full
                                text-sm text-slate-500
                                file:mr-4
                                file:py-2
                                file:px-4
                                file:rounded-full
                                file:border-0
                                file:text-sm
                                file:font-semibold
                                file:bg-violet-50
                                file:text-violet-700
                                hover:file:bg-violet-100
                              "
                              :class="{ 'is-invalid': errors.receipt }"
                              @input="
                                weeklyPayForm.receipt = $event.target.files[0]
                              "
                            />
                          </label>
                          <div
                            v-if="errors.receipt"
                            v-text="errors.receipt"
                            class="text-red-500"
                          ></div>
                        </div>

                        <button
                          class="
                            inline-block
                            px-6
                            py-2.5
                            bg-purple-600
                            text-white
                            font-medium
                            text-sm
                            leading-tight
                            capitalize
                            rounded
                            shadow-md
                            hover:bg-purple-700 hover:shadow-lg
                            focus:bg-purple-700
                            focus:shadow-lg
                            focus:outline-none
                            focus:ring-0
                            active:bg-purple-800 active:shadow-lg
                            transition
                            duration-150
                            ease-in-out
                          "
                          type="submit"
                          :disabled="weeklyPayForm.processing"
                          :class="{ 'opacity-25': weeklyPayForm.processing }"
                          v-if="!editContent"
                        >
                          Save
                        </button>
                        <button
                          class="
                            inline-block
                            px-6
                            py-2.5
                            bg-purple-600
                            text-white
                            font-medium
                            text-sm
                            leading-tight
                            capitalize
                            rounded
                            shadow-md
                            hover:bg-purple-700 hover:shadow-lg
                            focus:bg-purple-700
                            focus:shadow-lg
                            focus:outline-none
                            focus:ring-0
                            active:bg-purple-800 active:shadow-lg
                            transition
                            duration-150
                            ease-in-out
                          "
                          type="submit"
                          :disabled="weeklyPayForm.processing"
                          :class="{ 'opacity-25': weeklyPayForm.processing }"
                          v-else
                        >
                          Update
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- end of weekly Pay form modal -->

            <div
              class="modal"
              id="manageModal"
              v-if="this.deleteWeeklyPayModal"
            >
              <div class="modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="flex justify-between items-center">
                      <h2 class="text-xl capitalize modal-title">
                        Delete weekly payment
                      </h2>
                      <button @click="hideDeleteWeeklyPayModal">
                        <span class="material-symbols-outlined">close</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body">
                    <Confirm
                      @destroy="destroyWeeklyPay(this.selectedWeeklyPayRow)"
                      @close="hideDeleteWeeklyPayModal"
                      >Are you sure you want to delete this record?</Confirm
                    >
                  </div>
                </div>
              </div>
            </div>
            <!-- end of delete weekly Pay modal-->
          </div>
        </div>

        <!-- end of weekly Pay -->

       <!-- monthly Pay -->
       <p class="mt-5 text-xl font-semibold text-gray-800 capitalize">
          monthly Payments
        </p>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="md:flex md:justify-between md:items-start gap-1.5 mb-4">
              <div
                class="
                  md:flex md:flex-wrap
                  lg:flex-nowrap
                  items-center
                  gap-1.5
                  md:w-1/2
                  lg:w-2/3
                "
              >
                <select
                  v-if="$page.props.auth.user.role == 'admin'"
                  v-model="monthlyPayFilter.country_id"
                  @change="fetchMonthlyPayBranches(), fetchMonthlyPays()"
                  class="
                    form-control
                    mt-2.5
                    md:mb-2.5
                    block
                    w-full
                    lg:w-1/5
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                >
                  <option value="" disabled>-- Select country --</option>
                  <option
                    v-for="country in countries"
                    :key="country.id"
                    :value="country.id"
                  >
                    {{ country.name }}
                  </option>
                </select>
                <select
                  v-if="$page.props.auth.user.role == 'admin'"
                  v-model="monthlyPayFilter.branch_id"
                  @change="fetchMonthlyPays"
                  class="
                    form-control
                    mt-2.5
                    md:mb-2.5
                    block
                    w-full
                    lg:w-48
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                >
                  <option value="" disabled>-- Select branch --</option>
                  <option
                    v-for="branch in monthly_pay_branches"
                    :key="branch.id"
                    :value="branch.id"
                  >
                    {{ branch.name }}
                  </option>
                  <option value="" v-if="monthly_pay_branches.length >= 1">
                    None
                  </option>
                </select>

                <input
                  v-model="monthlyPayFilter.from_date"
                  @change="fetchMonthlyPays"
                  type="date"
                  id="from_date"
                  class="
                    date
                    block
                    w-full
                    md:w-2/5
                    lg:w-40
                    mt-2.5
                    md:mb-2.5
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                />
                <span class="material-symbols-outlined flex justify-center">
                  trending_flat
                </span>
                <input
                  v-model="monthlyPayFilter.to_date"
                  @change="fetchMonthlyPays"
                  type="date"
                  class="
                    date
                    block
                    w-full
                    md:w-2/5
                    lg:w-40
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                />
              </div>

              <div>
              <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  mr-1.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="downloadMonthlyPayPDF"
              >
                Download PDF
              </button>

              <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="createMonthlyPay('monthly payment')"
              >
                Add payment
              </button>
            </div>
            </div>

            <div class="flex flex-col">
              <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                  <div class="overflow-x-auto">
                    <table id="monthly_pays_table" class="min-w-full">
                      <thead class="border-b bg-gray-100 font-medium">
                        <tr>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Date of Issue
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Reference
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Quantity
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Amount
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Total
                          </th>
                          <th
                            scope="col"
                            class="text-sm text-gray-900 px-6 py-4 text-left"
                          >
                            Action
                          </th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal" id="manageModal" v-if="this.monthlyPayModal">
              <div class="modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="flex justify-between items-center">
                      <h2 class="text-xl capitalize modal-title">
                        {{ dataMethod }} monthly payment
                      </h2>
                      <button @click="hideMonthlyPayModal">
                        <span class="material-symbols-outlined">close</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body overscroll-auto">
                    <form
                      @submit.prevent="submitMonthlyPay"
                      class="mt-4 px-2 pb-2"
                      enctype="multipart/form-data"
                    >
                      <div class="row gx-3">
                        <div
                          class="col-12">
                          <label for="date"
                            >Date of Issue <span class="text-red-500">*</span></label
                          >
                          <input
                  v-model="monthlyPayForm.issue_date"
                  type="date"
                  class="
                    date
                    form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                  "
                  :max="this.today_date"
                  :class="{ 'is-invalid': errors.issue_date }"
                />
                          <div
                            v-if="errors.issue_date"
                            v-text="errors.issue_date"
                            class="text-red-500"
                          ></div>
                        </div>
                        <div class="col-12">
                          <label for="branch_id"
                            >Branch <span class="text-red-500">*</span></label
                          >
                          <select
                            id="branch_id"
                            v-if="editContent"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="monthlyPayForm.branch_id"
                            :class="{ 'is-invalid': errors.branch_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Branch --
                            </option>
                            <option
                              v-for="region in init_regions"
                              :key="region.id"
                              :value="region.id"
                            >
                              {{ region.name }}
                            </option>
                          </select>

                          <select
                            id="branch_id"
                            v-else
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="monthlyPayForm.branch_id"
                            :class="{ 'is-invalid': errors.branch_id }"
                          >
                            <option value="" disabled selected>
                              -- Select Branch --
                            </option>
                            <option
                              else
                              v-for="region in regions"
                              :key="region.id"
                              :value="region.id"
                            >
                              {{ region.name }}
                            </option>
                          </select>
                          <div
                            v-if="errors.branch_id"
                            v-text="errors.branch_id"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="title"
                            >Title <span class="text-red-500">*</span></label
                          >
                          <input
                            type="text"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="monthlyPayForm.title"
                            placeholder="Title"
                            :class="{ 'is-invalid': errors.title }"
                          />
                          <div
                            v-if="errors.title"
                            v-text="errors.title"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="quantity"
                            >Quantity <span class="text-red-500">*</span></label
                          >
                          <input
                            type="number"
                            class="
                              form-control
                              col
                              mt-1
                              block
                              w-full
                              rounded-md
                              border border-gray-300
                              bg-white
                              py-2
                              px-3
                              shadow-sm
                              focus:border-indigo-500
                              focus:outline-none
                              focus:ring-indigo-500
                              sm:text-sm
                            "
                            v-model="monthlyPayForm.quantity"
                            @keyup="calculateMonthlyPayAmount"
                            placeholder="Quantity"
                            :class="{ 'is-invalid': errors.quantity }"
                          />
                          <div
                            v-if="errors.quantity"
                            v-text="errors.quantity"
                            class="text-red-500"
                          ></div>
                        </div>

                        <div class="col-12">
                          <label for="amount"
                            >Amount (per head) <span class="text-red-500">*</span></label
                          >
                          <div class="relative">
                            <input
                              type="text"
                              id="hs-trailing-icon"
                              name="hs-trailing-icon"
                              class="
                                form-control
                                block
                                w-full
                                rounded-md
                                border border-gray-300
                                bg-white
                                py-2
                                px-3
                                shadow-sm
                                focus:border-indigo-500
                                focus:outline-none
                                focus:ring-indigo-500
                                sm:text-sm
                              "
                              v-model="monthlyPayForm.amount"
                              @keyup="calculateMonthlyPayAmount"
                              placeholder="Amount"
                              :class="{ 'is-invalid': errors.amount }"
                            />
                            <div
                              class="
                                absolute
                                inset-y-0
                                right-0
                                flex
                                items-center
                                pointer-events-none
                                z-20
                                pr-5
                              "
                            >
                              <span class="text-gray-500">{{
                                this.editContent
                                  ? this.currency
                                  : $page.props.auth.country.currency_code
                              }}</span>
                            </div>
                            <div
                              v-if="errors.amount"
                              v-text="errors.amount"
                              class="text-red-500"
                            ></div>
                          </div>
                        </div>

                        <div class="col-12">
                          <label for="amount"
                            >Total <span class="text-red-500">*</span></label
                          >
                          <div class="relative">
                            <input
                              type="text"
                              id="hs-trailing-icon"
                              name="hs-trailing-icon"
                              class="
                                block
                                w-full
                                rounded-md
                                border border-gray-300
                                bg-white
                                py-2
                                px-3
                                shadow-sm
                                focus:outline-none
                                focus:border-gray-300
                                focus:ring-gray-300
                                sm:text-sm
                                read-only:bg-slate-100
                              "
                              v-model="monthlyPayForm.total"
                              placeholder="Total"
                              :readonly="true"
                            />
                            <div
                              class="
                                absolute
                                inset-y-0
                                right-0
                                flex
                                items-center
                                pointer-events-none
                                z-20
                                pr-5
                              "
                            >
                              <span class="text-gray-500">{{
                                this.editContent
                                  ? this.currency
                                  : $page.props.auth.country.currency_code
                              }}</span>
                            </div>
                            <div
                              v-if="errors.total"
                              v-text="errors.total"
                              class="text-red-500"
                            ></div>
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="shrink-0" v-if="editContent">
                            <ReceiptImage
                              :src="monthlyPayReceipt"
                              alt="Current receipt"
                            />
                          </div>
                          <div
                            class="flex items-center mb-3"
                            v-if="editContent"
                          >
                            <input
                              type="checkbox"
                              class="
                                default:ring-2
                                text-purple-600
                                rounded-sm
                                focus:ring-purple-500
                              "
                              @change="
                                this.monthlyPayForm.update =
                                  !this.monthlyPayForm.update
                              "
                            />
                            <p class="pl-2">Update receipt</p>
                          </div>

                          <label
                            class="block"
                            v-if="monthlyPayForm.update || !editContent"
                            >Receipt <span class="text-red-500">*</span>
                            <span class="sr-only">Choose receipt</span>
                            <input
                              type="file"
                              accept="image/*"
                              class="
                                block
                                w-full
                                text-sm text-slate-500
                                file:mr-4
                                file:py-2
                                file:px-4
                                file:rounded-full
                                file:border-0
                                file:text-sm
                                file:font-semibold
                                file:bg-violet-50
                                file:text-violet-700
                                hover:file:bg-violet-100
                              "
                              :class="{ 'is-invalid': errors.receipt }"
                              @input="
                                monthlyPayForm.receipt = $event.target.files[0]
                              "
                            />
                          </label>
                          <div
                            v-if="errors.receipt"
                            v-text="errors.receipt"
                            class="text-red-500"
                          ></div>
                        </div>

                        <button
                          class="
                            inline-block
                            px-6
                            py-2.5
                            bg-purple-600
                            text-white
                            font-medium
                            text-sm
                            leading-tight
                            capitalize
                            rounded
                            shadow-md
                            hover:bg-purple-700 hover:shadow-lg
                            focus:bg-purple-700
                            focus:shadow-lg
                            focus:outline-none
                            focus:ring-0
                            active:bg-purple-800 active:shadow-lg
                            transition
                            duration-150
                            ease-in-out
                          "
                          type="submit"
                          :disabled="monthlyPayForm.processing"
                          :class="{ 'opacity-25': monthlyPayForm.processing }"
                          v-if="!editContent"
                        >
                          Save
                        </button>
                        <button
                          class="
                            inline-block
                            px-6
                            py-2.5
                            bg-purple-600
                            text-white
                            font-medium
                            text-sm
                            leading-tight
                            capitalize
                            rounded
                            shadow-md
                            hover:bg-purple-700 hover:shadow-lg
                            focus:bg-purple-700
                            focus:shadow-lg
                            focus:outline-none
                            focus:ring-0
                            active:bg-purple-800 active:shadow-lg
                            transition
                            duration-150
                            ease-in-out
                          "
                          type="submit"
                          :disabled="monthlyPayForm.processing"
                          :class="{ 'opacity-25': monthlyPayForm.processing }"
                          v-else
                        >
                          Update
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- end of monthly Pay form modal -->

            <div
              class="modal"
              id="manageModal"
              v-if="this.deleteMonthlyPayModal"
            >
              <div class="modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="flex justify-between items-center">
                      <h2 class="text-xl capitalize modal-title">
                        Delete monthly payment
                      </h2>
                      <button @click="hideDeleteMonthlyPayModal">
                        <span class="material-symbols-outlined">close</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body">
                    <Confirm
                      @destroy="destroyMonthlyPay(this.selectedMonthlyPayRow)"
                      @close="hideDeleteMonthlyPayModal"
                      >Are you sure you want to delete this record?</Confirm
                    >
                  </div>
                </div>
              </div>
            </div>
            <!-- end of delete monthly Pay modal-->
          </div>
        </div>

        <!-- end of monthly Pay -->

        <!-- activities -->
        <div v-if="$page.props.auth.user.role == 'admin'">
          <p class="mt-5 text-xl font-semibold text-gray-800 capitalize">
            Activities
          </p>
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="md:flex gap-1.5 items-center justify-between mb-4">
                <div class="flex items-center gap-1.5">
                <input
                  type="date"
                  v-model="activityFilter.from_date"
                  class="
                    date
                    block
                    w-full
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                  @change="fetchActivities"
                />
                <span class="material-symbols-outlined flex justify-center">
                  trending_flat
                </span>
                <input
                  type="date"
                  v-model="activityFilter.to_date"
                  class="
                    date
                    block
                    w-full
                    rounded-md
                    border border-gray-300
                    bg-white
                    py-2
                    px-3
                    shadow-sm
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-indigo-500
                    sm:text-sm
                  "
                  :max="this.today_date"
                  @change="fetchActivities"
                />
                </div>
                <button
                type="button"
                class="
                  inline-block
                  mt-2.5
                  mr-1.5
                  md:mb-2.5
                  px-6
                  py-2.5
                  bg-purple-600
                  text-white
                  font-bold
                  text-sm
                  leading-tight
                  rounded
                  shadow-md
                  hover:bg-purple-700 hover:shadow-lg
                  focus:bg-purple-700
                  focus:shadow-lg
                  focus:outline-none
                  focus:ring-0
                  active:bg-purple-800 active:shadow-lg
                  transition
                  duration-150
                  ease-in-out
                "
                @click="downloadActivitiesPDF"
              >
                Download PDF
              </button>
              </div>

              <div class="flex flex-col">
                <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                  <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="overflow-x-auto">
                      <table id="activities_table" class="min-w-full">
                        <thead class="border-b bg-gray-100 font-medium">
                          <tr>
                            <th
                              scope="col"
                              class="text-sm text-gray-900 px-6 py-4 text-left"
                            >
                              Date
                            </th>
                            <th
                              scope="col"
                              class="text-sm text-gray-900 px-6 py-4 text-left"
                            >
                              Status
                            </th>
                            <th
                              scope="col"
                              class="text-sm text-gray-900 px-6 py-4 text-left"
                            >
                              Description
                            </th>
                            <th
                              scope="col"
                              class="text-sm text-gray-900 px-6 py-4 text-left"
                            >
                              Action
                            </th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="modal"
                id="manageModal"
                v-if="this.deleteActivitiesModal"
              >
                <div class="modal-sm">
                  <div class="modal-content">
                    <div class="modal-header">
                      <div class="flex justify-between items-center">
                        <h2 class="text-xl capitalize modal-title">
                          Delete activity
                        </h2>
                        <button @click="hideDeleteActivitiesModal">
                          <span class="material-symbols-outlined">close</span>
                        </button>
                      </div>
                    </div>
                    <div class="modal-body">
                      <Confirm
                        @destroy="destroyActivities(this.selectedActivitiesRow)"
                        @close="hideDeleteActivitiesModal"
                        >Are you sure you want to delete this record?</Confirm
                      >
                    </div>
                  </div>
                </div>
              </div>
              <!-- end of delete activities modal-->
            </div>
          </div>
        </div>
        <!-- end of activities -->
      </div>
    </div>
  </AuthenticatedLayout>
</template>
