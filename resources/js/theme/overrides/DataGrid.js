/* eslint-disable no-undef */
export default function DataGrid(theme) {
  return {
    MuiDataGrid: {
      defaultProps: { nonce: __webpack_nonce__ },

      styleOverrides: {
        root: {
          borderRadius: 0,
          border: `1px solid transparent`,
          '& .MuiTablePagination-root': {
            borderTop: 0
          },
          '& .MuiDataGrid-toolbarContainer': {
            padding: theme.spacing(2),
            backgroundColor: theme.palette.background.neutral,
            '& .MuiButton-root': {
              marginRight: theme.spacing(1.5),
              color: theme.palette.text.primary,
              '&:hover': {
                backgroundColor: theme.palette.action.hover
              }
            }
          },
          '& .MuiDataGrid-cell, .MuiDataGrid-columnsContainer': {
            borderBottom: `1px solid ${theme.palette.divider}`
          },
          '& .MuiDataGrid-columnSeparator': {
            color: theme.palette.divider
          },
          '& .MuiDataGrid-columnHeader[data-field="__check__"]': {
            padding: 0
          },
          '& .pointer': {
            cursor: 'pointer'
          }
        },
        columnHeader: {
          '&:focus, &:focus-within, &:focus-visible': {
            outline: 'none'
          }
        },
        cell: {
          '&:focus, &:focus-within, &:focus-visible': {
            outline: 'none'
          }
        }
      }
    },
    MuiGridMenu: {
      styleOverrides: {
        root: {
          '& .MuiDataGrid-gridMenuList': {
            boxShadow: theme.customShadows.z20,
            borderRadius: 5
          },
          '& .MuiMenuItem-root': {
            ...theme.typography.body2
          }
        }
      }
    },
    MuiGridFilterForm: {
      styleOverrides: {
        root: {
          padding: theme.spacing(1.5, 0),
          '& .MuiFormControl-root': {
            margin: theme.spacing(0, 0.5)
          },
          '& .MuiInput-root': {
            marginTop: theme.spacing(3),
            '&::before, &::after': {
              display: 'none'
            },
            '& .MuiNativeSelect-select, .MuiInput-input': {
              ...theme.typography.body2,
              padding: theme.spacing(0.75, 1),
              borderRadius: 5,
              backgroundColor: theme.palette.background.neutral
            },
            '& .MuiSvgIcon-root': {
              right: 4
            }
          }
        }
      }
    },
    MuiGridPanelFooter: {
      styleOverrides: {
        root: {
          padding: theme.spacing(2),
          justifyContent: 'flex-end',
          '& .MuiButton-root': {
            '&:first-of-type': {
              marginRight: theme.spacing(1.5),
              color: theme.palette.text.primary,
              '&:hover': {
                backgroundColor: theme.palette.action.hover
              }
            },
            '&:last-of-type': {
              color: theme.palette.common.white,
              backgroundColor: theme.palette.primary.main,
              '&:hover': {
                backgroundColor: theme.palette.primary.dark
              }
            }
          }
        }
      }
    }
  };
}
