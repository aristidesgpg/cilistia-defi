import React, {
    Fragment,
    useCallback,
    useContext,
    useEffect,
    useState
} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import {
    Box,
    Grid,
    IconButton,
    InputAdornment,
    MenuItem,
    Stack
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import Form, {SelectAdornment, TextField} from "components/Form";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {isEmpty} from "lodash";
import {notify} from "utils/index";
import {useSupportedCurrencies} from "hooks/global";
import Spin from "components/Spin";
import CardGiftcardIcon from "@mui/icons-material/CardGiftcard";
import {LoadingButton} from "@mui/lab";
import UploadPhoto from "components/UploadPhoto";
import TableContext from "contexts/TableContext";
import ModalContent from "components/ModalContent";
import ModalActions from "components/ModalActions";

const messages = defineMessages({
    success: {defaultMessage: "Giftcard was updated."},
    editGiftcard: {defaultMessage: "Edit Giftcard"},
    name: {defaultMessage: "Giftcard"},
    brand: {defaultMessage: "Brand"},
    title: {defaultMessage: "Title"},
    label: {defaultMessage: "Label"},
    description: {defaultMessage: "Description"},
    instruction: {defaultMessage: "Instruction"},
    caption: {defaultMessage: "Allowed *.jpeg, *.jpg, *.png"},
    value: {defaultMessage: "Value"},
    currency: {defaultMessage: "Currency"},
    giftcards: {defaultMessage: "Giftcards"}
});

const GiftcardEdit = ({giftcard}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const editGiftcard = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.editGiftcard),
            content: <EditForm giftcard={giftcard} />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl, giftcard]);

    return (
        <Fragment>
            <IconButton onClick={editGiftcard}>
                <EditIcon />
            </IconButton>

            {modalElements}
        </Fragment>
    );
};

const EditForm = ({closeModal, giftcard}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [brands, setBrands] = useState([]);
    const {reload: reloadTable} = useContext(TableContext);
    const [formRequest, formLoading] = useFormRequest(form);
    const [request, loading] = useRequest();

    const fetchBrands = useCallback(() => {
        request
            .get(route("admin.giftcard-brand.all"))
            .then((data) => setBrands(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchBrands();
    }, [fetchBrands]);

    const {currencies} = useSupportedCurrencies();

    const submitForm = useCallback(
        (values) => {
            const url = route("admin.giftcard.update", {giftcard: giftcard.id});

            formRequest
                .put(url, values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, intl, giftcard, reloadTable]
    );

    const currencySelect = (
        <Form.Item
            name="currency"
            initialValue={giftcard.currency}
            rules={[{required: true}]}>
            <SelectAdornment>
                {currencies.map((currency) => (
                    <MenuItem value={currency.code} key={currency.code}>
                        {currency.code}
                    </MenuItem>
                ))}
            </SelectAdornment>
        </Form.Item>
    );

    const valueField = (
        <Stack
            alignItems="center"
            justifyContent="center"
            sx={{height: "100%"}}
            spacing={2}>
            <Form.Item
                name="label"
                initialValue={giftcard.label}
                label={intl.formatMessage(messages.label)}
                rules={[{required: true}]}>
                <TextField fullWidth />
            </Form.Item>

            <Form.Item
                name="value"
                initialValue={giftcard.value}
                label={intl.formatMessage(messages.value)}
                rules={[{required: true}]}>
                <TextField
                    type="number"
                    fullWidth
                    InputProps={{
                        endAdornment: (
                            <InputAdornment position="end">
                                {currencySelect}
                            </InputAdornment>
                        )
                    }}
                />
            </Form.Item>
        </Stack>
    );

    const uploadUrl = route("admin.giftcard.upload-thumbnail", {
        giftcard: giftcard.id
    });

    return (
        <Form form={form} onFinish={submitForm}>
            <Spin spinning={loading}>
                <ModalContent spacing={2}>
                    {!isEmpty(brands) && (
                        <Form.Item
                            name="brand_id"
                            initialValue={giftcard.brand.id}
                            label={intl.formatMessage(messages.brand)}
                            rules={[{required: true}]}>
                            <TextField
                                fullWidth
                                InputProps={{
                                    startAdornment: (
                                        <InputAdornment position="start">
                                            <CardGiftcardIcon />
                                        </InputAdornment>
                                    )
                                }}
                                select>
                                {brands.map((brand) => (
                                    <MenuItem value={brand.id} key={brand.id}>
                                        {brand.name}
                                    </MenuItem>
                                ))}
                            </TextField>
                        </Form.Item>
                    )}

                    <Form.Item
                        name="title"
                        initialValue={giftcard.title}
                        label={intl.formatMessage(messages.title)}
                        rules={[{required: true}]}>
                        <TextField fullWidth />
                    </Form.Item>

                    <Box sx={{py: 1}}>
                        <Grid container spacing={3}>
                            <Grid item xs={6}>
                                <UploadPhoto
                                    action={uploadUrl}
                                    preview={giftcard.thumbnail}
                                    onSuccess={reloadTable}
                                    rounded={true}
                                />
                            </Grid>
                            <Grid item xs={6}>
                                {valueField}
                            </Grid>
                        </Grid>
                    </Box>

                    <Form.Item
                        name="description"
                        initialValue={giftcard.description}
                        label={intl.formatMessage(messages.description)}>
                        <TextField fullWidth multiline rows={3} />
                    </Form.Item>

                    <Form.Item
                        name="instruction"
                        initialValue={giftcard.instruction}
                        label={intl.formatMessage(messages.instruction)}>
                        <TextField fullWidth multiline rows={3} />
                    </Form.Item>
                </ModalContent>

                <ModalActions>
                    <LoadingButton
                        variant="contained"
                        loading={formLoading}
                        disabled={loading}
                        type="submit">
                        <FormattedMessage defaultMessage="Submit" />
                    </LoadingButton>
                </ModalActions>
            </Spin>
        </Form>
    );
};

export default GiftcardEdit;
