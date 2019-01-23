port module Admin.BoardStaff exposing (main, Profile, decodeProfile)

import Array.Hamt as Array exposing (Array)
import Admin.Utils
    exposing
        ( adminGet
        , adminPost
        , formLabel
        , formRow
        , SubmissionStatus(AwaitingResponse)
        , initialSubmissionStatus
        , statusFromWebData
        , submissionNotice
        , submissionSpinner
        , submissionAwaitingResponse
        )
import Html
    exposing
        ( Html
        , h1
        , h2
        , h3
        , div
        , span
        , form
        , table
        , tr
        , th
        , text
        , p
        , code
        , hr
        , input
        , textarea
        , button
        )
import Html.Attributes
    exposing
        ( type_
        , class
        , disabled
        , style
        , required
        , value
        , placeholder
        , id
        , rows
        , cols
        )
import Html.Events exposing (onClick, onInput, onSubmit)
import Json.Decode as Decode exposing (Decoder)
import Json.Encode as Encode exposing (Value)
import RemoteData exposing (WebData)


main : Program Flags Model Msg
main =
    Html.programWithFlags
        { init = init
        , update = update
        , view = view
        , subscriptions = always Sub.none
        }


type alias Flags =
    { nonce : String }


{-| Scroll to the top of the page & unfocus any selected inputs.
-}
port scrollToTop : () -> Cmd msg



-- Model


type alias Model =
    { board : Array Profile
    , staff : Array Profile
    , fetchError : Bool
    , formStatus : SubmissionStatus
    , formError : String
    , wpNonce : String
    }


type alias Profile =
    { name : String
    , image : String
    , bio : String
    }


newProfile : Profile
newProfile =
    Profile "" "" ""


encodeProfile : Profile -> Value
encodeProfile p =
    Encode.object
        [ ( "name", Encode.string p.name )
        , ( "image", Encode.string p.image )
        , ( "bio", Encode.string p.bio )
        ]


decodeProfile : Decoder Profile
decodeProfile =
    Decode.map3 Profile
        (Decode.field "name" Decode.string)
        (Decode.field "image" Decode.string)
        (Decode.field "bio" Decode.string)


init : Flags -> ( Model, Cmd Msg )
init flags =
    let
        model =
            { wpNonce = flags.nonce
            , board = Array.empty
            , staff = Array.empty
            , fetchError = False
            , formStatus = initialSubmissionStatus
            , formError = ""
            }
    in
        ( model, getData model )



-- Update


type Msg
    = FetchData (WebData ( Array Profile, Array Profile ))
    | SaveData (WebData (Result String ()))
    | StaffProfile ProfileMsg
    | BoardProfile ProfileMsg
    | SubmitForm


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        FetchData (RemoteData.Success ( board, staff )) ->
            ( { model | board = board, staff = staff }, Cmd.none )

        FetchData _ ->
            ( { model | fetchError = True }, Cmd.none )

        SaveData resp ->
            let
                formStatus =
                    statusFromWebData resp

                error =
                    case resp of
                        RemoteData.Success (Ok _) ->
                            ""

                        RemoteData.Success (Err err) ->
                            err

                        _ ->
                            model.formError
            in
                ( { model | formStatus = formStatus, formError = error }
                , scrollToTop ()
                )

        StaffProfile subMsg ->
            ( { model | staff = updateProfile subMsg model.staff }, Cmd.none )

        BoardProfile subMsg ->
            ( { model | board = updateProfile subMsg model.board }, Cmd.none )

        SubmitForm ->
            ( { model | formStatus = AwaitingResponse }, saveData model )


type ProfileMsg
    = AddProfile
    | ProfileName Int String
    | ProfileImage Int String
    | ProfileBio Int String
    | MoveProfileUp Int
    | MoveProfileDown Int
    | DeleteProfile Int


updateProfile : ProfileMsg -> Array Profile -> Array Profile
updateProfile msg profiles =
    case msg of
        AddProfile ->
            Array.push newProfile profiles

        ProfileName index name ->
            updateArray index profiles <| \p -> { p | name = name }

        ProfileImage index image ->
            updateArray index profiles <| \p -> { p | image = image }

        ProfileBio index bio ->
            updateArray index profiles <| \p -> { p | bio = bio }

        MoveProfileUp index ->
            moveUp index profiles

        MoveProfileDown index ->
            moveDown index profiles

        DeleteProfile index ->
            deleteArray index profiles



-- Commands


{-| Fetch the initial Profile data
-}
getData : Model -> Cmd Msg
getData m =
    let
        decoder =
            Decode.map2 (,)
                (Decode.field "board" decodeProfiles)
                (Decode.field "staff" decodeProfiles)

        decodeProfiles =
            Decode.map Array.fromList <| Decode.list decodeProfile
    in
        adminGet "board-staff/get/" m decoder
            |> Cmd.map FetchData


{-| Save the Profile data to Wordpress's DB.
-}
saveData : Model -> Cmd Msg
saveData m =
    let
        decoder =
            Decode.oneOf
                [ Decode.map Err <| Decode.field "errors" Decode.string
                , Decode.map Ok <| Decode.succeed ()
                ]

        body =
            Encode.object
                [ ( "board", Encode.list <| List.map encodeProfile <| Array.toList m.board )
                , ( "staff", Encode.list <| List.map encodeProfile <| Array.toList m.staff )
                ]
    in
        adminPost "board-staff/set/" m body decoder
            |> Cmd.map SaveData



-- View


view : Model -> Html Msg
view model =
    if model.fetchError then
        div []
            [ h1 [] [ text "FIC Board & Staff Profiles" ]
            , p [ style [ ( "color", "red" ), ( "font-weight", "bold" ) ] ]
                [ text "There was an error fetching the data. This is a bug, report it!" ]
            ]
    else
        form [ onSubmit SubmitForm ]
            [ h1 [] [ text "FIC Board & Staff Profiles" ]
            , submissionNotice model.formStatus
                "The Profiles were successfully saved."
            , p [] [ text "This form allows you to change the content of the Board & Staff page in a structured way." ]
            , p []
                [ text "Use two line breaks in the Biography fields to indicate paragraph breaks. Use "
                , code [] [ text "[link name](http://linkurl.com)" ]
                , text " to render links. Surround text in asterisks to make it italics, "
                , code [] [ text "*like so*" ]
                , text "."
                ]
            , h2 [ style [ ( "font-size", "1.7em" ) ] ]
                [ text "Staff" ]
            , profileForms StaffProfile "staff" model.staff
            , h2 [ style [ ( "margin-top", "2rem" ), ( "font-size", "1.7em" ) ] ]
                [ text "Board" ]
            , profileForms BoardProfile "board" model.board
            , hr [ style [ ( "margin", "2rem 0" ) ] ] []
            , button
                [ type_ "submit"
                , class "button-primary"
                , disabled <| submissionAwaitingResponse model.formStatus
                ]
                [ text "Update" ]
            , submissionSpinner model.formStatus
            ]


{-| Render a single set of Profile forms.
-}
profileForms : (ProfileMsg -> Msg) -> String -> Array Profile -> Html Msg
profileForms msg prefix profiles =
    let
        profileForm i profile =
            let
                makeId name =
                    prefix ++ toString i ++ name
            in
                div [ style [ ( "clear", "both" ) ] ]
                    [ h3 [] [ text profile.name ]
                    , div []
                        [ button
                            [ type_ "button"
                            , class "button"
                            , onClick <| msg <| MoveProfileUp i
                            ]
                            [ span
                                [ class "dashicons dashicons-arrow-up-alt"
                                , style [ ( "margin-top", "0.15em" ) ]
                                ]
                                []
                            ]
                        , button
                            [ type_ "button"
                            , class "button"
                            , onClick <| msg <| MoveProfileDown i
                            ]
                            [ span
                                [ class "dashicons dashicons-arrow-down-alt"
                                , style [ ( "margin-top", "0.15em" ) ]
                                ]
                                []
                            ]
                        ]
                    , table [ class "form-table" ]
                        [ formRow (formLabel (makeId "name") "Name") <|
                            input
                                [ type_ "text"
                                , required True
                                , value profile.name
                                , placeholder "Jane Doe"
                                , onInput <| msg << ProfileName i
                                , id <| makeId "name"
                                ]
                                []
                        , formRow (formLabel (makeId "image") "Image") <|
                            input
                                [ type_ "text"
                                , required True
                                , value profile.image
                                , placeholder "/wp-content/uploads/..."
                                , onInput <| msg << ProfileImage i
                                , id <| makeId "image"
                                ]
                                []
                        , formRow (formLabel (makeId "bio") "Biography") <|
                            textarea
                                [ required True
                                , placeholder "Enter a bio for the person."
                                , onInput <| msg << ProfileBio i
                                , id <| makeId "bio"
                                , rows 20
                                , cols 55
                                ]
                                [ text profile.bio ]
                        ]
                    , button
                        [ type_ "button"
                        , class "button-primary"
                        , onClick <| msg <| DeleteProfile i
                        , style [ ( "margin-bottom", "2rem" ) ]
                        ]
                        [ text "Delete Profile" ]
                    ]
    in
        div [] <|
            (List.intersperse (hr [] []) <|
                Array.toList <|
                    Array.indexedMap profileForm profiles
            )
                ++ [ if Array.length profiles > 0 then
                        hr [] []
                     else
                        text ""
                   , button
                        [ type_ "button"
                        , class "button"
                        , onClick <| msg AddProfile
                        ]
                        [ text "Add Profile" ]
                   ]



-- Utils


updateArray : Int -> Array a -> (a -> a) -> Array a
updateArray i arr f =
    case Array.get i arr of
        Nothing ->
            arr

        Just el ->
            Array.set i (f el) arr


deleteArray : Int -> Array a -> Array a
deleteArray index arr =
    let
        left =
            Array.slice 0 index arr

        right =
            Array.slice (index + 1) (Array.length arr) arr
    in
        Array.append left right


{-| Move an index up in the array.
-}
moveUp : Int -> Array a -> Array a
moveUp index array =
    if index <= 0 then
        array
    else
        swapIndexes index (index - 1) array


{-| Move an index down in the array.
-}
moveDown : Int -> Array a -> Array a
moveDown index array =
    if index >= (Array.length array - 1) then
        array
    else
        swapIndexes index (index + 1) array


{-| Swap the values at the given indexes in an array
-}
swapIndexes : Int -> Int -> Array a -> Array a
swapIndexes fromIndex toIndex initialArray =
    Array.get toIndex initialArray
        |> Maybe.andThen
            (\toItem ->
                Array.get fromIndex initialArray
                    |> Maybe.map (\fromItem -> Array.set toIndex fromItem initialArray)
                    |> Maybe.map (\array -> Array.set fromIndex toItem array)
            )
        |> Maybe.withDefault initialArray
